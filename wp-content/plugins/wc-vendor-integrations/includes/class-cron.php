
<?php
class WVI_Cron {
  static function init(){
    add_filter('cron_schedules',function($s){
      $s['wvi_5min']=['interval'=>300,'display'=>'co 5 min'];
      return $s;
    });
    add_action('wvi_sync_tick',[__CLASS__,'tick']);
    add_action('wvi_sync_categories_tick',[__CLASS__,'sync_cats']);
  }
  static function tick(){
    $vendors=get_users(['role__in'=>['wcfm_vendor','vendor','seller']]);
    foreach($vendors as $u){ WVI_Sync::sync_vendor($u->ID); }
  }
  static function sync_cats(){
    WVI_Admin_Categories::sync_now();
  }
}
WVI_Cron::init();
