<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
/**
 * Plugin Name:       Nevo Export
 * Plugin URI:        https://nevothemes.com/nevo-export
 * Description:       Nevo Export with Options to Export Widget, Customizer and Media Files
 * Version:           1.0.0
 * Author:            NevoThemes
 * Author URI:        https://nevothemes.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nevo-export
 * Domain Path:       /languages
 */

/*Define Constants for this plugin*/
define( 'NEVO_EXPORT_VERSION', '1.0.0' );
define( 'NEVO_EXPORT_PATH', plugin_dir_path( __FILE__ ) );
define( 'NEVO_EXPORT_URL', plugin_dir_url( __FILE__ ) );

$upload_dir                   = wp_upload_dir();
$nevo_export_temp         = $upload_dir['basedir'] . '/nevo-export-temp/';
$nevo_export_temp_uploads = $nevo_export_temp . '/uploads/';

define( 'NEVO_EXPORT_TEMP', $nevo_export_temp );
define( 'NEVO_EXPORT_TEMP_UPLOADS', $nevo_export_temp_uploads );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nevo-export-activator.php
 */
function activate_nevo_export() {
	require_once NEVO_EXPORT_PATH . 'includes/class-nevo-export-activator.php';
	Nevo_Export_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nevo-export-deactivator.php
 */
function deactivate_nevo_export() {
	require_once NEVO_EXPORT_PATH . 'includes/class-nevo-export-deactivator.php';
	Nevo_Export_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_nevo_export' );
register_deactivation_hook( __FILE__, 'deactivate_nevo_export' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require NEVO_EXPORT_PATH . 'includes/class-nevo-export.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function nevo_export() {
	return Nevo_Export::instance();
}
nevo_export();
