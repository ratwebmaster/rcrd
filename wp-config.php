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
define( 'DB_NAME', 'ratcityrollerderby' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',         'ldL&_ue,!{b[Be]`1=6y0=1whEW>Z~M7?2;-rY8,*x`CYi]|!QlHf*egKMgmtR;V' );
define( 'SECURE_AUTH_KEY',  'sq8SMmaJ!. =hxGF}D40aY$45$Plw0ZrvC+lGZNqEw78`;t;C3w3txHE*y:Gxqcj' );
define( 'LOGGED_IN_KEY',    '81&o>9iMDpTD@.[+Vq_uGt[f +$)iM.vXL|WF&I|h[)fI8cy/1f1g2wB{[LGM=m.' );
define( 'NONCE_KEY',        'tBW )<7J5-S#QXMd,@d~Quc6~v5f10o(G7M$%{ J.2&7zogVu=D3aVqq/T(AH$B@' );
define( 'AUTH_SALT',        '@EQ#]u*i5ln@1q#+&!5xD:rRs1wyxsJLr,;O4(;y3tNMt$2X8Bi_?/;DcI;=tRxn' );
define( 'SECURE_AUTH_SALT', '3z@&Q(`NAMH{^o458v#{d1jb&fwgcEQhaJ>ow_W^K`r2apKd8ZDp:sjZr1K[ws)6' );
define( 'LOGGED_IN_SALT',   'CPBG$=o#gRn6/Ts`@2Y5D:fxoGP0E[t5K>oQ@;khoLcxUt;^]n**nEByph-T[OFT' );
define( 'NONCE_SALT',       'EG 7v&S=k%`<6FU[2,xX^Q/,8]qV{-:->%XLUe<01}xT9Y9cM6?wc&K@sLEPNSjx' );

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
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
