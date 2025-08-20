
<?php
class WVI_OAuth {
  static function init() {
    add_action('rest_api_init', function(){
      register_rest_route('wc-vendor-integrations/v1','/oauth/allegro/callback',['methods'=>'GET','callback'=>['WVI_OAuth','allegro'],'permission_callback'=>'__return_true']);
      register_rest_route('wc-vendor-integrations/v1','/oauth/olx/callback',['methods'=>'GET','callback'=>['WVI_OAuth','olx'],'permission_callback'=>'__return_true']);
    });
  }
  static function parse_state($state){ list($n,$uid)=explode('|',$state); if(!wp_verify_nonce($n,'wvi_state')) return false; return intval($uid); }

  static function allegro($req){
    $code=$req['code'];$state=$req['state'];$uid=self::parse_state($state); if(!$uid) return new WP_Error('bad_state','State error');
    $client_id=get_option('wvi_allegro_client_id');$client_secret=get_option('wvi_allegro_client_secret');$redirect=rest_url('wc-vendor-integrations/v1/oauth/allegro/callback');
    $resp=wp_remote_post('https://allegro.pl/auth/oauth/token',[ 'headers'=>['Authorization'=>'Basic '.base64_encode($client_id.':'.$client_secret)], 'body'=>['grant_type'=>'authorization_code','code'=>$code,'redirect_uri'=>$redirect] ]);
    if(is_wp_error($resp)) return $resp; $d=json_decode(wp_remote_retrieve_body($resp),true);
    if(empty($d['access_token'])) return new WP_Error('no_token','Brak tokenu');
    $seller_id=null;$me=wp_remote_get('https://api.allegro.pl/me',['headers'=>['Authorization'=>'Bearer '.$d['access_token'],'Accept'=>'application/vnd.allegro.public.v1+json']]);
    if(!is_wp_error($me)&&wp_remote_retrieve_response_code($me)===200){$meData=json_decode(wp_remote_retrieve_body($me),true);$seller_id=$meData['id']??null;}
    update_user_meta($uid,'_wvi_allegro',['connected'=>true,'seller_id'=>$seller_id,'access_token'=>$d['access_token'],'refresh_token'=>$d['refresh_token'],'expires_at'=>time()+intval($d['expires_in'])]);
    return new WP_REST_Response('OK',200);
  }
  static function olx($req){
    $code=$req['code'];$state=$req['state'];$uid=self::parse_state($state); if(!$uid) return new WP_Error('bad_state','State error');
    $client_id=get_option('wvi_olx_client_id');$client_secret=get_option('wvi_olx_client_secret');$redirect=rest_url('wc-vendor-integrations/v1/oauth/olx/callback');
    $resp=wp_remote_post('https://www.olx.pl/api/open/oauth/token',[ 'headers'=>['Authorization'=>'Basic '.base64_encode($client_id.':'.$client_secret)], 'body'=>['grant_type'=>'authorization_code','code'=>$code,'redirect_uri'=>$redirect] ]);
    if(is_wp_error($resp)) return $resp; $d=json_decode(wp_remote_retrieve_body($resp),true);
    if(empty($d['access_token'])) return new WP_Error('no_token','Brak tokenu');
    update_user_meta($uid,'_wvi_olx',['connected'=>true,'access_token'=>$d['access_token'],'refresh_token'=>$d['refresh_token'],'expires_at'=>time()+intval($d['expires_in'])]);
    return new WP_REST_Response('OK',200);
  }
}
WVI_OAuth::init();
