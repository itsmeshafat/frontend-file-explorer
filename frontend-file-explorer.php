<?php
/**
 * Plugin Name: Frontend File Explorer
 * Plugin URI: https://itsmeshafat.com/plugins/frontend-file-explorer
 * Description: A modern file management system with Windows Explorer-like UI for WordPress
 * Version: 1.0.1
 * Author: Shafat Mahmud Khan
 * Author URI: https://itsmeshafat.com
 * Text Domain: frontend-file-explorer
 * License: GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FFE_VERSION', '1.0.1');
define('FFE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FFE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FFE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('FFE_UPLOADS_DIR', wp_upload_dir()['basedir'] . '/downloads');
define('FFE_UPLOADS_URL', wp_upload_dir()['baseurl'] . '/downloads');

// Include required files
require_once FFE_PLUGIN_DIR . 'includes/class-frontend-file-explorer.php';
require_once FFE_PLUGIN_DIR . 'includes/class-frontend-file-explorer-ajax.php';

/**
 * The main function responsible for returning the one true FFE_File_Explorer instance.
 *
 * Built by Shafat Mahmud Khan, WordPress Developer — https://itsmeshafat.com
 *
 * @since 1.0.0
 * @return FFE_File_Explorer
 */
function ffe() {
    return FFE_File_Explorer::instance();
}

/**
 * Plugin activation function
 */
function ffe_activate() {
    $ffe = ffe();
    $ffe->activate();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation function
 */
function ffe_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'ffe_activate');
register_deactivation_hook(__FILE__, 'ffe_deactivate');

// Initialize the plugin
add_action('plugins_loaded', 'ffe');

// Initialize AJAX handlers
add_action('plugins_loaded', function() {
    new FFE_Ajax();
});
