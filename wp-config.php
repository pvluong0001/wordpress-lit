<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'I~L1YjBf[rZ7;oj{oxi~kUp@d*LOhq<H$9VG%4-K c.IDt`Y+>bm,C((*b[?,|NQ' );
define( 'SECURE_AUTH_KEY',  'Jx7h([ewtx+K{bTh75b]y~}lY1ZyD~3N$`Qa`%6rd o[J!eP>mAv&<]e<s=*r!eq' );
define( 'LOGGED_IN_KEY',    'L@(1E3I++W-GkzGAUr(QH0ANbGzsZx38nsL54@`+nZiX|y:G4vn&(I^lDoL`temB' );
define( 'NONCE_KEY',        '8`x}{s!WG`N)6+__#*>j6Cnj`sy#8Esh*DM.?|T{:,En<A@eM_4~Hjx}3YYB]u;Z' );
define( 'AUTH_SALT',        ',}waY;uUHM<lz|>=>)<9n?HC^$^TMa8HN5~rzo|B;~IbAe,w;Z^G }kU7)l^:;a=' );
define( 'SECURE_AUTH_SALT', 'L;@~gs{;@2g|0n7{d+K~Vm9,!Y!A;>f)sNN08e4nY-%r5!k=|S7!Mp2ek6|(]9b?' );
define( 'LOGGED_IN_SALT',   '5._7tUR58g;Kbo>%x)-EWld03FZ%-EP}gj;}*YlT]]Ruw,?VE]0,oW&Ztd6-brWH' );
define( 'NONCE_SALT',       'wSMi_JU$ GO02Vt*?35iX1d+!H^J2>UE5+vO9VU~6-Z/gkAOPvK9te~kh] ,i>0F' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
