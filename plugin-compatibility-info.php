<?php
/**
 * Plugin Name:       Plugin Compatibility Info
 * Plugin URI:        http://codismo.com/plugins/plugin-compatibility-info
 * Description:       Shows the version of WordPress that your plugins have been tested up to. For more information click the "Visit plugin site" link below.
 * Version:           1.0.0
 * Author:            Codismo
 * Author URI:        http://codismo.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-compatibility-info
 * Domain Path:       /languages
 */

// called directly, abort
if ( ! defined( 'WPINC' ) ) {
	die;
}

// constants
define( 'PLUGIN_COMPATIBILITY_INFO_VERSION', '1.0.0' );
define( 'PLUGIN_COMPATIBILITY_INFO_URL', plugin_dir_url( __FILE__ ) );
define( 'PLUGIN_COMPATIBILITY_INFO_BASENAME', plugin_basename( __FILE__ ) );
define( 'PLUGIN_COMPATIBILITY_INFO_DIR_NAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'PLUGIN_COMPATIBILITY_INFO_ABS', dirname(__FILE__) );

// include
include PLUGIN_COMPATIBILITY_INFO_ABS . '/inc/class.general.php';