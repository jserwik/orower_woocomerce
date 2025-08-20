
<?php
class WVI_Admin_Categories {
  static function render(){
    if(!current_user_can('manage_options')) wp_die('Brak uprawnień');
    if(isset($_POST['wvi_sync_categories']) && check_admin_referer('wvi_admin_cats')){
      self::sync_now();
      echo '<div class="updated"><p>Odświeżono kategorie z Allegro i OLX (PL).</p></div>';
    }
    if(isset($_POST['wvi_save_mapping']) && check_admin_referer('wvi_admin_cats')){
      $map = [];
      if(!empty($_POST['map']) && is_array($_POST['map'])){
        foreach($_POST['map'] as $olxId => $wooIds){
          $wooIds = array_map('intval', (array)$wooIds);
          $map[$olxId] = array_values(array_filter($wooIds));
        }
      }
      update_option('wvi_olx_cat_map', $map);
      echo '<div class="updated"><p>Zapisano mapowanie OLX → Woo.</p></div>';
    }
    $olx_cats = get_option('wvi_cached_olx_cats', []);
    $bike_cats = array_filter($olx_cats, function($c){
      $name = isset($c['name']) ? $c['name'] : ( $c['localized_name'] ?? '' );
      return WVI_Util::is_bike_related($name);
    });
    $woo_terms = get_terms(['taxonomy'=>'product_cat','hide_empty'=>false]);
    $map = get_option('wvi_olx_cat_map', []);
    ?>
    <div class="wrap">
      <h1>Kategorie & Mapowanie (OLX → Woo)</h1>
      <form method="post">
        <?php wp_nonce_field('wvi_admin_cats'); ?>
        <p>
          <button class="button button-primary" name="wvi_sync_categories" value="1">Pobierz / zaktualizuj kategorie z Allegro i OLX</button>
        </p>
        <h2>Mapowanie kategorii OLX (rowerowe) do WooCommerce</h2>
        <table class="widefat striped">
          <thead><tr><th>OLX kategoria</th><th>Przypisz do kategorii Woo (wiele)</th></tr></thead>
          <tbody>
          <?php foreach($bike_cats as $c): 
            $id = $c['id'] ?? ($c['code'] ?? '');
            $name = $c['name'] ?? ($c['localized_name'] ?? $id);
            $selected = $map[$id] ?? [];
          ?>
            <tr>
              <td><strong><?php echo esc_html($name); ?></strong><br><small>ID: <?php echo esc_html($id); ?></small></td>
              <td>
                <select name="map[<?php echo esc_attr($id); ?>][]" multiple style="min-width: 320px; height: 120px;">
                  <?php foreach($woo_terms as $t): ?>
                    <option value="<?php echo esc_attr($t->term_id); ?>" <?php selected(in_array($t->term_id, $selected)); ?>>
                      <?php echo esc_html($t->name); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p><?php submit_button('Zapisz mapowanie', 'secondary', 'wvi_save_mapping', false); ?></p>
      </form>
    </div>
    <?php
  }

  static function sync_now(){
    $vendors = get_users(['role__in'=>['wcfm_vendor','vendor','seller']]);
    foreach($vendors as $u){
      $al = get_user_meta($u->ID,'_wvi_allegro',true);
      if(!empty($al['access_token'])){
        $cats = AllegroService::fetch_categories_tree($al['access_token']);
        update_option('wvi_cached_allegro_cats', $cats);
        break;
      }
    }
    foreach($vendors as $u){
      $ol = get_user_meta($u->ID,'_wvi_olx',true);
      if(!empty($ol['access_token'])){
        $cats = OlxService::categories($ol['access_token']);
        update_option('wvi_cached_olx_cats', $cats);
        break;
      }
    }
    $bikeFromAl = array_filter(get_option('wvi_cached_allegro_cats', []), function($c){ return WVI_Util::is_bike_related($c['name'] ?? ''); });
    foreach($vendors as $u){
      $parent = WVI_Sync::ensure_vendor_parent_category($u->ID, 'Allegro');
      foreach($bikeFromAl as $c){
        WVI_Sync::ensure_wc_category($u->ID, $c['name'], 'allegro', $c['id'], $parent);
      }
    }
    $bikeFromOl = array_filter(get_option('wvi_cached_olx_cats', []), function($c){
      $nm = $c['name'] ?? ($c['localized_name'] ?? '');
      return WVI_Util::is_bike_related($nm);
    });
    foreach($vendors as $u){
      $parent = WVI_Sync::ensure_vendor_parent_category($u->ID, 'OLX');
      foreach($bikeFromOl as $c){
        $id = $c['id'] ?? ($c['code'] ?? null);
        $nm = $c['name'] ?? ($c['localized_name'] ?? 'OLX');
        if($id) WVI_Sync::ensure_wc_category($u->ID, $nm, 'olx', $id, $parent);
      }
    }
  }
}
