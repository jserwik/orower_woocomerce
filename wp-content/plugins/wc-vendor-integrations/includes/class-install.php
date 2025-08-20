
<?php
class WVI_Install {
  static function install() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $table = $wpdb->prefix . 'wvi_logs';
    $sql = "CREATE TABLE $table (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      user_id BIGINT UNSIGNED NOT NULL,
      channel VARCHAR(20) NOT NULL,
      message TEXT,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    ) $charset;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    $map = $wpdb->prefix . 'wvi_product_map';
    $sql2 = "CREATE TABLE $map (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
      user_id BIGINT UNSIGNED NOT NULL,
      channel VARCHAR(20) NOT NULL,
      external_id VARCHAR(190) NOT NULL,
      woo_product_id BIGINT UNSIGNED NOT NULL,
      category_external_id VARCHAR(190) NULL,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY(id),
      UNIQUE KEY unique_item (user_id, channel, external_id)
    ) $charset;";
    dbDelta($sql2);
    add_option('wvi_db_version', WVI_DB_VERSION);
    add_option('wvi_cached_allegro_cats', []);
    add_option('wvi_cached_olx_cats', []);
    add_option('wvi_olx_cat_map', []);
    if (!wp_next_scheduled('wvi_sync_tick')) {
      wp_schedule_event(time()+60, 'wvi_5min', 'wvi_sync_tick');
    }
    if (!wp_next_scheduled('wvi_sync_categories_tick')) {
      wp_schedule_event(time()+120, 'hourly', 'wvi_sync_categories_tick');
    }
  }
  static function deactivate() {
    $ts = wp_next_scheduled('wvi_sync_tick');
    if ($ts) wp_unschedule_event($ts, 'wvi_sync_tick');
    $ts2 = wp_next_scheduled('wvi_sync_categories_tick');
    if ($ts2) wp_unschedule_event($ts2, 'wvi_sync_categories_tick');
  }
}
