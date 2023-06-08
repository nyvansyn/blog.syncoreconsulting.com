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
define( 'DB_NAME', 'synconsulting_blog' );

/** Database username */
define( 'DB_USER', 'synconsulting_admin' );

/** Database password */
define( 'DB_PASSWORD', 'syncore12345' );

/** Database hostname */
define( 'DB_HOST', 'localhost:6844' );

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
define('AUTH_KEY', '9E6*!yY0_p[L(U5Pt8M48q&5Gm264Wqu6e6JtKw6]i8c&T3y7/G9uqsZ+U;#S0S:');
define('SECURE_AUTH_KEY', 'g6)||3@isa)F!Z1])_*8Wu[tQ)|30|JjtL56;[(Ut2M&mo1~SXtUG3oB!xf*Iz3z');
define('LOGGED_IN_KEY', 'SnQAxB53sTE&2h)UE:28~f@2ZKKY+YTlO_83V[CmzGQbze[*h8rrgZ|87NxQ!j9V');
define('NONCE_KEY', 'AO%:50]4pu2!9)G#)_GB8[868xhErWz_MaJ*sA300QH_~;F;;rQmr30|+H&+8a5[');
define('AUTH_SALT', 'vlf#m137O:7b|yq591p#TU2c20U459n3X+@jx8*N4*:)ec_I6lIpN6M!~38b:B+1');
define('SECURE_AUTH_SALT', 'o)k_4~l;Zz6kpGnlG!9%~a+n3EN6GrX(UMAfFG12pRJ8C-jH+p:IgI1b!l7Iz+xf');
define('LOGGED_IN_SALT', 'p!u3A%@8/X2|K432Js!d3_|ny@452vh1Y!g!3hW!+f3DKbn1|eD~5zP[5m;90Fvm');
define('NONCE_SALT', '_MSp0nW@TSftIP*o|2C2UFj2X61:(40_XOw35(4r+21~97SyAz%9)6]y#w|0913M');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'blog_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
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
