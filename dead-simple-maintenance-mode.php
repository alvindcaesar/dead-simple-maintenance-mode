<?php
/**
 * Plugin Name:     Dead Simple Maintenance Mode
 * Plugin URI:      https://codepathics.com
 * Description:     Simple and bloat free maintenance mode for your website
 * Author:          Codepathics
 * Author URI:      https://codepathics.com
 * Text Domain:     dead-simple-maintenance-mode
 * Domain Path:     /languages
 * Version:         1.1
 */

defined( 'WPINC' ) || die;

// Define plugin constants
define( 'DSMM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DSMM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DSMM_PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( 'DSMM_TEXT_DOMAIN', 'dead-simple-maintenance-mode' );
define( 'DSMM_PLUGIN_VERSION', '1.1' );

// Require the main plugin class
require_once DSMM_PLUGIN_PATH . 'includes/class-dsm-mode.php';

// Initialize the plugin
function dsmm_init() {
    return DSM_Mode::instance();
}
add_action( 'plugins_loaded', 'dsmm_init' );

// Register deactivation hook
register_deactivation_hook( __FILE__, 'dsmm_deactivate' );

function dsmm_deactivate() {
    delete_option( 'dsmm_options' );
}

