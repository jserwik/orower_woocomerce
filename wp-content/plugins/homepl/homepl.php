<?php
/*
 * Plugin Name: home.pl - configuration
 * Description:
 * Version: 1.0.0
 * Author: home.pl S.A.
 * Author URI: http://home.pl
 *
 */

function wp_add_dashboard_widgets() {
    wp_add_dashboard_widget(
        'wp_dashboard_widget',
	'Dokończ konfigurację',
	'wp_new_dashboard_widget_function',
	'',
	'',
	'normal',
	'high'
    );
}

add_action( 'wp_dashboard_setup', 'wp_add_dashboard_widgets' );

function wp_new_dashboard_widget_function() {
    echo "Zainstalowaliśmy najważniejsze moduły sklepowe.<html><br><a href=\"https://pomoc.home.pl/baza-wiedzy/wtyczki-woocommerce-i-prestashop-jak-uruchomic-kluczowe-uslugi-w-swoim-sklepie-internetowym?utm_source=dashboard_prestashop&utm_medium=notyfikacja&utm_campaign=rj\">Sprawdź</a>, jak dokończyć ich konfigurację i skorzystaj z rabatów jako Klient home.pl.</html>";
}

