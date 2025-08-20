
<?php
class WVI_Util {
  static function is_bike_related($name){
    $name = mb_strtolower((string)$name);
    $needles = ['rower','rowery','rowerowy','bike','bicycle','kolarsk','mtb','kask','spodenki','koszulka','hamulec','przerzutka','opona','pedał','siodełko','bidon','lampka','pompka'];
    foreach($needles as $n){ if($n!=='' && strpos($name,$n)!==false) return true; }
    return false;
  }
  static function sideload_images($post_id, $urls){
    if(empty($urls)) return [];
    if(!function_exists('media_sideload_image')) require_once ABSPATH . 'wp-admin/includes/media.php';
    $ids = [];
    foreach($urls as $u){
      $att_id = media_sideload_image($u, $post_id, null, 'id');
      if(!is_wp_error($att_id)){ $ids[] = (int)$att_id; }
    }
    if(!empty($ids)){
      set_post_thumbnail($post_id, $ids[0]);
      if(count($ids)>1){ $gallery = implode(',', array_slice($ids,1)); update_post_meta($post_id, '_product_image_gallery', $gallery); }
    }
    return $ids;
  }
}

class AllegroService {
  static function headers($token){ return ['Authorization'=>'Bearer '.$token,'Accept'=>'application/vnd.allegro.public.v1+json','Content-Type'=>'application/json']; }
  static function fetch_categories_tree($token, $parentId = null, $acc = []){
    $url = 'https://api.allegro.pl/sale/categories'; if($parentId){ $url .= '?parent.id='.urlencode($parentId); }
    $res = wp_remote_get($url, ['headers'=>self::headers($token),'timeout'=>30]); if(is_wp_error($res)) return $acc;
    $data = json_decode(wp_remote_retrieve_body($res), true); if(empty($data['categories'])) return $acc;
    foreach($data['categories'] as $cat){ $acc[] = $cat; if(empty($cat['leaf'])){ $acc = self::fetch_categories_tree($token, $cat['id'], $acc); } }
    return $acc;
  }
  static function seller_offers($token, $seller_id, $page=1, $acc=[]){
    $url = add_query_arg([ 'seller.id'=>$seller_id, 'offset'=>($page-1)*60, 'limit'=>60, 'fallback'=>'true' ], 'https://api.allegro.pl/offers/listing');
    $res = wp_remote_get($url, ['headers'=>self::headers($token),'timeout'=>30]); if(is_wp_error($res)) return $acc;
    $data = json_decode(wp_remote_retrieve_body($res), true); $items = array_merge($data['items']['promoted'] ?? [], $data['items']['regular'] ?? []); $acc = array_merge($acc, $items);
    $count = intval($data['searchMeta']['availableCount'] ?? 0); if($page*60 < $count){ return self::seller_offers($token,$seller_id,$page+1,$acc); }
    return $acc;
  }
  static function extract_images($offer){
    $imgs = [];
    if(!empty($offer['images'])){ foreach($offer['images'] as $i){ if(!empty($i['url'])) $imgs[] = $i['url']; } }
    if(empty($imgs) && !empty($offer['thumbnail']['url'])) $imgs[] = $offer['thumbnail']['url'];
    return array_values(array_unique($imgs));
  }
}

class OlxService {
  static function headers($token){ return ['Authorization'=>'Bearer '.$token,'Content-Type'=>'application/json']; }
  static function categories($token){ $url='https://www.olx.pl/api/open/v1/categories'; $res=wp_remote_get($url, ['headers'=>self::headers($token),'timeout'=>30]); if(is_wp_error($res)) return []; $data=json_decode(wp_remote_retrieve_body($res),true); return $data['data'] ?? $data['categories'] ?? []; }
  static function user_offers($token, $page=1, $acc=[]){
    $url= add_query_arg(['limit'=>50,'offset'=>($page-1)*50], 'https://www.olx.pl/api/open/v1/offers');
    $res=wp_remote_get($url, ['headers'=>self::headers($token),'timeout'=>30]); if(is_wp_error($res)) return $acc;
    $data=json_decode(wp_remote_retrieve_body($res),true); $items = $data['data'] ?? []; $acc = array_merge($acc, $items);
    if(!empty($data['links']['next'])){ return self::user_offers($token,$page+1,$acc); }
    return $acc;
  }
  static function extract_images($offer){
    $imgs = [];
    if(!empty($offer['photos'])){ foreach($offer['photos'] as $p){ if(!empty($p['url'])) $imgs[] = $p['url']; if(!empty($p['link'])) $imgs[] = $p['link']; } }
    if(!empty($offer['images'])){ foreach($offer['images'] as $i){ if(!empty($i['url'])) $imgs[] = $i['url']; } }
    return array_values(array_unique($imgs));
  }
}
