<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', '33227024_c2e94745' );

/** Database username */
define( 'DB_USER', '33227024_c2e94745' );

/** Database password */
define( 'DB_PASSWORD', 'rco8DZuM' );

/** Database hostname */
define( 'DB_HOST', 'mysql8' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'A*Qs~)C|#Bn@E}X-(,Fm8.emTt()HXA~-$DB6{ipJAR>9%Z2-C%f,Hy&YMo?R9[b' );
define( 'SECURE_AUTH_KEY',   '4Y0?QZ4{w{~3{^ H]{Od`N].%6PS$nE1v-Inw.B; 9l+gcF%$?E)R;]3LS{5+(/o' );
define( 'LOGGED_IN_KEY',     ']5p(m7rSKf$^Jqt[n2lnW?:Sl_boKEw$ymlOh$#[MTZ?W9,#hR3Uh5WGWBKjZSBX' );
define( 'NONCE_KEY',         '.FA$MuICaV{oQgL(?l9&Ay Ec]</SEVQi%w!RP[u.j6-eFOlCR0mZ<RSN3QCTjHL' );
define( 'AUTH_SALT',         'NBv%bX?D1f.]iq/EZv4VW&)OH%!y-O@erkfDks}p.bn-8B7PeC6Vn^h)&n)^jsp;' );
define( 'SECURE_AUTH_SALT',  'ecTE!Q%|)1hRM>je{5*?qaFNY=z;P=Zp$88f9X&N]Xso~$krMUXBkxP.cBrFyqr<' );
define( 'LOGGED_IN_SALT',    'xiz|~9W%U.oS=PrB5TMG_<ucPq@0D(E~;8|8e!<q9u5& m&.beH0,f]X*mRzcD~p' );
define( 'NONCE_SALT',        'jdj-Wh~;^4eWxIv5.p:x|l`3:S7->j2`8$, [tmvHY]3PZp]kW#5IX}3|[o{pv(r' );
define( 'WP_CACHE_KEY_SALT', 'c47JYFC~arM`K}oQn6S+LT q4gT;xMvFH%vWyOVYWv.S+2vvl$9nw^ZuKNk<1N r' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_MEMORY_LIMIT', '4096M'); define('WP_MAX_MEMORY_LIMIT', '4096M');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
