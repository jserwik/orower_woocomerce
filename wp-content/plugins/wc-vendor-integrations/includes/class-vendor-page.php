
<?php
class WVI_Vendor_Page {
  static function init() {
    add_filter('wcfm_menus', [__CLASS__, 'menus']);
    add_filter('wcfm_query_vars', [__CLASS__, 'query_vars']);
    add_filter('wcfm_endpoint_title', [__CLASS__, 'endpoint_title'], 10, 2);
    add_action('wcfm_endpoint_vendor-integrations', [__CLASS__, 'render_page']);
    add_action('wp_ajax_wvi_connect_allegro', [__CLASS__, 'connect_allegro']);
    add_action('wp_ajax_wvi_disconnect_allegro', [__CLASS__, 'disconnect_allegro']);
    add_action('wp_ajax_wvi_connect_olx', [__CLASS__, 'connect_olx']);
    add_action('wp_ajax_wvi_disconnect_olx', [__CLASS__, 'disconnect_olx']);
    add_action('wp_ajax_wvi_save_base_token', [__CLASS__, 'save_base_token']);
    add_action('wp_ajax_wvi_manual_sync', [__CLASS__, 'manual_sync']);
  }
  static function menus($menus) {
    if ( current_user_can('wcfm_vendor') ) {
      $menus['vendor-integrations'] = [
        'label' => __('Integracje','wvi'),
        'url'   => wcfm_get_endpoint_url('vendor-integrations'),
        'icon'  => 'fa-plug',
        'priority'=>55,
      ];
    }
    return $menus;
  }
  static function query_vars($vars){ $vars['vendor-integrations']='vendor-integrations'; return $vars; }
  static function endpoint_title($title,$endpoint){ if($endpoint==='vendor-integrations') $title=__('Integracje','wvi'); return $title; }

  static function render_page() {
    $uid = get_current_user_id();
    $al = get_user_meta($uid,'_wvi_allegro',true);
    $ol = get_user_meta($uid,'_wvi_olx',true);
    $bl = get_user_meta($uid,'_wvi_base',true);
    $nonce = wp_create_nonce('wvi_actions');
    echo '<div class="wcfm-container"><div class="wcfm-content"><h2>Integracje</h2>';
    echo '<h3>Allegro</h3>';
    if(!empty($al['connected'])){
      echo '<p>Połączono z Allegro (seller_id: '.esc_html($al['seller_id']).')</p>';
      echo '<a class="wcfm_submit_button" href="'.esc_url(admin_url('admin-ajax.php?action=wvi_disconnect_allegro&_wpnonce='.$nonce)).'">Odłącz</a>';
    } else {
      echo '<a class="wcfm_submit_button" href="'.esc_url(admin_url('admin-ajax.php?action=wvi_connect_allegro&_wpnonce='.$nonce)).'">Połącz</a>';
    }
    echo '<h3>OLX (Polska)</h3>';
    if(!empty($ol['connected'])){
      echo '<p>Połączono z OLX PL</p>';
      echo '<a class="wcfm_submit_button" href="'.esc_url(admin_url('admin-ajax.php?action=wvi_disconnect_olx&_wpnonce='.$nonce)).'">Odłącz</a>';
    } else {
      echo '<a class="wcfm_submit_button" href="'.esc_url(admin_url('admin-ajax.php?action=wvi_connect_olx&_wpnonce='.$nonce)).'">Połącz</a>';
    }
    echo '<hr/><h3>Synchronizacja (kategorie + oferty Kup Teraz – rowerowe, z obrazami)</h3>';
    echo '<form method="post" action="'.esc_url(admin_url('admin-ajax.php')).'">';
    echo '<input type="hidden" name="action" value="wvi_manual_sync">';
    echo wp_nonce_field('wvi_actions','_wpnonce',true,false);
    echo '<button type="submit" class="wcfm_submit_button">Uruchom teraz</button>';
    echo '</form>';
    echo '</div></div>';
  }

  static function connect_allegro(){ check_admin_referer('wvi_actions'); $client_id=get_option('wvi_allegro_client_id'); $redirect=rest_url('wc-vendor-integrations/v1/oauth/allegro/callback'); $state=wp_create_nonce('wvi_state').'|'.get_current_user_id(); $url='https://allegro.pl/auth/oauth/authorize?'.http_build_query(['response_type'=>'code','client_id'=>$client_id,'redirect_uri'=>$redirect,'state'=>$state,'scope'=>'allegro:all']); wp_redirect($url);exit; }
  static function disconnect_allegro(){ check_admin_referer('wvi_actions'); delete_user_meta(get_current_user_id(),'_wvi_allegro'); wp_safe_redirect(wcfm_get_endpoint_url('vendor-integrations'));exit; }
  static function connect_olx(){ check_admin_referer('wvi_actions'); $client_id=get_option('wvi_olx_client_id'); $redirect=rest_url('wc-vendor-integrations/v1/oauth/olx/callback'); $state=wp_create_nonce('wvi_state').'|'.get_current_user_id(); $url='https://www.olx.pl/api/open/oauth/authorize?'.http_build_query(['response_type'=>'code','client_id'=>$client_id,'redirect_uri'=>$redirect,'state'=>$state,'scope'=>'read write']); wp_redirect($url);exit; }
  static function disconnect_olx(){ check_admin_referer('wvi_actions'); delete_user_meta(get_current_user_id(),'_wvi_olx'); wp_safe_redirect(wcfm_get_endpoint_url('vendor-integrations'));exit; }
  static function save_base_token(){ check_admin_referer('wvi_actions'); $uid=get_current_user_id(); $token=sanitize_text_field($_POST['bl_token']); $resp=wp_remote_post('https://api.baselinker.com/connector.php',['headers'=>['X-BLToken'=>$token],'body'=>['method'=>'getJournalList','parameters'=>wp_json_encode(['limit'=>1])], 'timeout'=>15 ]); $ok=!is_wp_error($resp)&&wp_remote_retrieve_response_code($resp)===200; update_user_meta($uid,'_wvi_base',['token'=>$token,'valid'=>$ok]); wp_safe_redirect(wcfm_get_endpoint_url('vendor-integrations'));exit; }
  static function manual_sync(){ check_admin_referer('wvi_actions'); WVI_Sync::sync_vendor(get_current_user_id()); wp_safe_redirect(wcfm_get_endpoint_url('vendor-integrations'));exit; }
}
WVI_Vendor_Page::init();
