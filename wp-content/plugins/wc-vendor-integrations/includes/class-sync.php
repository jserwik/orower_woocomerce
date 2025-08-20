
<?php
class WVI_Sync {
  static function sync_vendor($uid){
    $al = get_user_meta($uid,'_wvi_allegro',true);
    if(!empty($al['connected'])){
      self::sync_allegro_categories($uid,$al);
      self::sync_allegro_offers($uid,$al);
    }
    $ol = get_user_meta($uid,'_wvi_olx',true);
    if(!empty($ol['connected'])){
      self::sync_olx_categories($uid,$ol);
      self::sync_olx_offers($uid,$ol);
    }
  }
  static function sync_allegro_categories($uid,$al){
    $cats = AllegroService::fetch_categories_tree($al['access_token']);
    $parent_term_id = self::ensure_vendor_parent_category($uid, 'Allegro');
    foreach($cats as $c){
      $name = $c['name'] ?? '';
      if(!$name) continue;
      if(!WVI_Util::is_bike_related($name)) continue;
      self::ensure_wc_category($uid, $name, 'allegro', $c['id'], $parent_term_id);
    }
  }
  static function sync_allegro_offers($uid,$al){
    if(empty($al['seller_id'])) return;
    $offers = AllegroService::seller_offers($al['access_token'], $al['seller_id']);
    foreach($offers as $o){
      $buyingMode = strtoupper($o['sellingMode']['format'] ?? ($o['buyingMode'] ?? ''));
      if($buyingMode !== 'BUY_NOW') continue;
      $title = $o['name'] ?? ($o['title'] ?? '');
      if(!$title || !WVI_Util::is_bike_related($title)) continue;
      $catId = $o['category']['id'] ?? null; $term_id = null;
      if($catId){ $term_id = self::ensure_wc_category($uid, $o['category']['name'] ?? 'Allegro', 'allegro', $catId, self::ensure_vendor_parent_category($uid,'Allegro')); }
      $price = null; if(isset($o['sellingMode']['price']['amount'])) $price = $o['sellingMode']['price']['amount']; elseif(isset($o['price']['amount'])) $price = $o['price']['amount'];
      $desc = $o['description'] ?? '';
      $product_id = self::upsert_product($uid, 'allegro', $o['id'], ['title'=>$title,'price'=>$price,'description'=>$desc,'term_id'=>$term_id]);
      $imgs = AllegroService::extract_images($o); if($product_id && !empty($imgs)){ WVI_Util::sideload_images($product_id, $imgs); }
    }
  }
  static function sync_olx_categories($uid,$ol){
    $cats = OlxService::categories($ol['access_token']);
    update_option('wvi_cached_olx_cats', $cats);
    $parent_term_id = self::ensure_vendor_parent_category($uid, 'OLX');
    foreach($cats as $c){
      $name = $c['name'] ?? ($c['localized_name'] ?? '');
      if(!$name) continue;
      if(!WVI_Util::is_bike_related($name)) continue;
      $id = $c['id'] ?? ($c['code'] ?? null);
      if(!$id) continue;
      self::ensure_wc_category($uid, $name, 'olx', $id, $parent_term_id);
    }
  }
  static function sync_olx_offers($uid,$ol){
    $offers = OlxService::user_offers($ol['access_token']);
    $map = get_option('wvi_olx_cat_map', []);
    foreach($offers as $o){
      $buy_now = false;
      if(isset($o['params'])){ foreach($o['params'] as $p){ $key = $p['key'] ?? ''; $val = is_array($p['value'] ?? null) ? implode(',', $p['value']) : ($p['value'] ?? ''); if(in_array($key, ['buy_now','fixed_price','price_type']) && (stripos($val,'buy')!==false || stripos($val,'fixed')!==false)) $buy_now = true; } }
      if(isset($o['type']) && in_array(strtolower($o['type']), ['buy_now','fixed','sell'])) $buy_now = true;
      if(!$buy_now) continue;
      $title = $o['title'] ?? '';
      if(!$title || !WVI_Util::is_bike_related($title)) continue;
      $olxCatId = $o['category']['id'] ?? ($o['category_id'] ?? null);
      $term_ids = [];
      if($olxCatId && !empty($map[$olxCatId])){ $term_ids = array_map('intval', (array)$map[$olxCatId]); }
      elseif($olxCatId){ $term_ids[] = self::ensure_wc_category($uid, $o['category']['name'] ?? 'OLX', 'olx', $olxCatId, self::ensure_vendor_parent_category($uid,'OLX')); }
      $price = null; if(isset($o['price']['value'])) $price = $o['price']['value'];
      $desc = $o['description'] ?? '';
      $product_id = self::upsert_product($uid, 'olx', $o['id'], ['title'=>$title,'price'=>$price,'description'=>$desc]);
      if($product_id && !empty($term_ids)){ wp_set_post_terms($product_id, $term_ids, 'product_cat', false); }
      $imgs = OlxService::extract_images($o); if($product_id && !empty($imgs)){ WVI_Util::sideload_images($product_id, $imgs); }
    }
  }
  static function ensure_vendor_parent_category($uid, $channelName){ if(get_option('wvi_vendor_categories_parent_mode','on')!=='on') return 0; $user = get_userdata($uid); $name = $channelName . ' - ' . $user->display_name; $slug = sanitize_title($name); $term = get_term_by('slug', $slug, 'product_cat'); if($term) return (int)$term->term_id; $new = wp_insert_term($name, 'product_cat', ['slug'=>$slug, 'parent'=>0]); if(is_wp_error($new)) return 0; return (int)$new['term_id']; }
  static function ensure_wc_category($uid, $name, $channel, $external_id, $parent_id=0){ $slug = sanitize_title($name . '-' . $channel . '-' . $external_id); $term = get_term_by('slug', $slug, 'product_cat'); if($term) return (int)$term->term_id; $args = ['slug'=>$slug]; if($parent_id){ $args['parent']=$parent_id; } $new = wp_insert_term($name, 'product_cat', $args); if(is_wp_error($new)) return 0; add_term_meta($new['term_id'], '_wvi_channel', $channel, true); add_term_meta($new['term_id'], '_wvi_external_id', $external_id, true); add_term_meta($new['term_id'], '_wvi_owner', $uid, true); return (int)$new['term_id']; }
  static function upsert_product($uid, $channel, $external_id, $data){ global $wpdb; $map = $wpdb->prefix.'wvi_product_map'; $found = $wpdb->get_row($wpdb->prepare("SELECT * FROM $map WHERE user_id=%d AND channel=%s AND external_id=%s",$uid,$channel,$external_id)); $post_data = ['post_title'=>sanitize_text_field($data['title'] ?? ''),'post_status'=>'publish','post_type'=>'product','post_content'=>wp_kses_post($data['description'] ?? ''),'post_author'=>$uid]; if($found){ $post_data['ID'] = (int)$found->woo_product_id; wp_update_post($post_data); $product_id = (int)$found->woo_product_id; } else { $product_id = wp_insert_post($post_data); if(is_wp_error($product_id)) return 0; $wpdb->insert($map, ['user_id'=>$uid,'channel'=>$channel,'external_id'=>$external_id,'woo_product_id'=>$product_id,'category_external_id'=>$data['category_external_id']??null]); update_post_meta($product_id, '_sku', $channel.'-'.$external_id); update_post_meta($product_id, '_manage_stock', 'no'); wp_set_object_terms($product_id, 'simple', 'product_type', false); } if(isset($data['price']) && $data['price']!==''){ if(function_exists('wc_format_decimal')){ $price = wc_format_decimal($data['price']); } else { $price = floatval($data['price']); } update_post_meta($product_id, '_regular_price', $price); update_post_meta($product_id, '_price', $price); } if(!empty($data['term_id'])){ wp_set_post_terms($product_id, [(int)$data['term_id']], 'product_cat', true); } return (int)$product_id; }
}
