
<?php
/**
 * Plugin Name: WC Vendor Integrations (WCFM) PRO Sync+Images
 * Description: Per-vendor integracje z Allegro (PL) i OLX (PL) + automatyczny import kategorii/produktów (Kup Teraz, rowery) z obrazami i mapowaniem kategorii OLX→Woo.
 * Version: 1.1.0
 * Author: Ty
 */

if ( ! defined('ABSPATH') ) exit;

define('WVI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WVI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WVI_DB_VERSION', '1.2');

require_once WVI_PLUGIN_DIR . 'includes/class-install.php';
require_once WVI_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once WVI_PLUGIN_DIR . 'includes/class-admin-categories.php';
require_once WVI_PLUGIN_DIR . 'includes/class-vendor-page.php';
require_once WVI_PLUGIN_DIR . 'includes/class-oauth.php';
require_once WVI_PLUGIN_DIR . 'includes/class-cron.php';
require_once WVI_PLUGIN_DIR . 'includes/class-services.php';
require_once WVI_PLUGIN_DIR . 'includes/class-sync.php';

register_activation_hook(__FILE__, ['WVI_Install', 'install']);
register_deactivation_hook(__FILE__, ['WVI_Install', 'deactivate']);
