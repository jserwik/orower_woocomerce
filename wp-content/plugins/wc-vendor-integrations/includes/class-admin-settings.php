
<?php
class WVI_Admin_Settings {
  static function init() {
    add_action('admin_menu', [__CLASS__, 'menu']);
    add_action('admin_init', [__CLASS__, 'register']);
  }
  static function menu() {
    add_menu_page('Vendor Integrations','Vendor Integrations','manage_options','wvi-settings',[__CLASS__,'render'],'dashicons-migrate',56);
    add_submenu_page('wvi-settings','Ustawienia API','Ustawienia API','manage_options','wvi-settings',[__CLASS__,'render']);
    add_submenu_page('wvi-settings','Kategorie & Mapowanie','Kategorie & Mapowanie','manage_options','wvi-categories',['WVI_Admin_Categories','render']);
  }
  static function register() {
    register_setting('wvi_settings', 'wvi_allegro_client_id');
    register_setting('wvi_settings', 'wvi_allegro_client_secret');
    register_setting('wvi_settings', 'wvi_olx_client_id');
    register_setting('wvi_settings', 'wvi_olx_client_secret');
    register_setting('wvi_settings', 'wvi_vendor_categories_parent_mode');
  }
  static function render() {
    $mode = get_option('wvi_vendor_categories_parent_mode', 'on');
    ?>
    <div class="wrap">
      <h1>Vendor Integrations – Ustawienia API</h1>
      <form method="post" action="options.php">
        <?php settings_fields('wvi_settings'); ?>
        <table class="form-table">
          <tr><th>Allegro Client ID</th><td><input type="text" name="wvi_allegro_client_id" value="<?php echo esc_attr(get_option('wvi_allegro_client_id')); ?>" class="regular-text"></td></tr>
          <tr><th>Allegro Client Secret</th><td><input type="password" name="wvi_allegro_client_secret" value="<?php echo esc_attr(get_option('wvi_allegro_client_secret')); ?>" class="regular-text"></td></tr>
          <tr><th>OLX Client ID (PL)</th><td><input type="text" name="wvi_olx_client_id" value="<?php echo esc_attr(get_option('wvi_olx_client_id')); ?>" class="regular-text"></td></tr>
          <tr><th>OLX Client Secret (PL)</th><td><input type="password" name="wvi_olx_client_secret" value="<?php echo esc_attr(get_option('wvi_olx_client_secret')); ?>" class="regular-text"></td></tr>
          <tr><th>Tryb kategorii per-vendor</th>
            <td>
              <select name="wvi_vendor_categories_parent_mode">
                <option value="on" <?php selected($mode,'on'); ?>>Twórz nadrzędną kategorię per vendor (zalecane)</option>
                <option value="off" <?php selected($mode,'off'); ?>>Dodawaj bezpośrednio do globalnych kategorii</option>
              </select>
            </td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
    </div>
    <?php
  }
}
WVI_Admin_Settings::init();
