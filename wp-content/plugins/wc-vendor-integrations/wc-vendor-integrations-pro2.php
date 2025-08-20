<?php
/*
Plugin Name: WC Vendor Integrations PRO2
Description: Integracja WCFM Vendor z Allegro i OLX (PL) - import kategorii, ofert (Kup Teraz), zdjęć, opisów + mapowanie kategorii OLX→Woo.
Version: 2.0
Author: ChatGPT
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('WVI_PRO2_PATH', plugin_dir_path(__FILE__));
define('WVI_PRO2_URL', plugin_dir_url(__FILE__));

require_once WVI_PRO2_PATH . 'includes/class-install.php';
require_once WVI_PRO2_PATH . 'includes/class-admin-settings.php';
require_once WVI_PRO2_PATH . 'includes/class-admin-categories.php';
require_once WVI_PRO2_PATH . 'includes/class-services.php';
require_once WVI_PRO2_PATH . 'includes/class-sync.php';
require_once WVI_PRO2_PATH . 'includes/class-cron.php';
require_once WVI_PRO2_PATH . 'includes/class-oauth.php';
require_once WVI_PRO2_PATH . 'includes/class-vendor-page.php';

register_activation_hook(__FILE__, ['WVI_Install','activate']);
register_deactivation_hook(__FILE__, ['WVI_Install','deactivate']);
