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


define('TTA_AUTHNET_LOGIN_ID', '27gj2N8BBr');
define('TTA_AUTHNET_TRANSACTION_KEY', '286zv6624KNw4QM7');
# Optional: set to 'false' for production
define('TTA_AUTHNET_SANDBOX', true);

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',          '6`x`fe8#,*5&W>kIcq#8&aCTbkdjGGqv6e>~qut0+2l&aOuH~2.rnU^?v^HixZH*' );
define( 'SECURE_AUTH_KEY',   'W[v?U$6A/.DI$Z&@]<P N9?~U5K&OG.A<.Cs0F&f%s2s^l~dhKl6)+&i~zj?J[k6' );
define( 'LOGGED_IN_KEY',     'LY}IP0z?}W&N~aq6:k,iW*d3=L%{9L]rKIp)SJt~j&&ed:`H!Qx+tk4|{<2EQ(xX' );
define( 'NONCE_KEY',         'B<o:FUwYP-4zeE>8,Z6Pqhe]c-/nMhv~Ru~(b%^<lx2@Ph$fK=`:]D,Dtyt_ZD?L' );
define( 'AUTH_SALT',         ' =;1W6^jZYQUC|J7Wj>I2o0C_~xa~d4I7GrO]pgz b3Fgj0.uLmI*Z`2jqg]KCch' );
define( 'SECURE_AUTH_SALT',  'p]>q8)C_JCv>cCA!>jw#.p9o=(Su4wVU~1!,sW(oFOUz0@B.aQk|aJn8&/_NuBm*' );
define( 'LOGGED_IN_SALT',    '&=5)=rdCKtYYRNR:^0Q.DMy9C}pK,X7HG7Z*V();d^L2gjs)CpVyU|(fxf2T>*wH' );
define( 'NONCE_SALT',        'BxL:cq>1V}a}INh-^n~KFPMG)/#*R $[D]sTF&CRTW`q;:!mEM;b-iHt%Rk*~Z^B' );
define( 'WP_CACHE_KEY_SALT', 'p2A=gJp2js3pB&G-=9<Qf(=quNbB5n% ,ZSJ,SM{(?PYa9EWTK;8C<8*Jpk7mNzC' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_j9bzlz98u3_';


/* Add any custom values between this line and the "stop editing" line. */



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

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
