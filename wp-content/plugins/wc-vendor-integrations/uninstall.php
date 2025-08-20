
<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
global $wpdb;
delete_option('wvi_db_version');
delete_option('wvi_allegro_client_id');
delete_option('wvi_allegro_client_secret');
delete_option('wvi_olx_client_id');
delete_option('wvi_olx_client_secret');
delete_option('wvi_vendor_categories_parent_mode');
delete_option('wvi_cached_allegro_cats');
delete_option('wvi_cached_olx_cats');
delete_option('wvi_olx_cat_map');
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wvi_logs");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wvi_product_map");
