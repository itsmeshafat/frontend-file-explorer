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
define('FRONTEND_FILE_EXPLORER_VERSION', '1.0.1');
define('FRONTEND_FILE_EXPLORER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FRONTEND_FILE_EXPLORER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FRONTEND_FILE_EXPLORER_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('FRONTEND_FILE_EXPLORER_UPLOADS_DIR', wp_upload_dir()['basedir'] . '/downloads');
define('FRONTEND_FILE_EXPLORER_UPLOADS_URL', wp_upload_dir()['baseurl'] . '/downloads');

// Include required files
require_once FRONTEND_FILE_EXPLORER_PLUGIN_DIR . 'includes/class-frontend-file-explorer.php';
require_once FRONTEND_FILE_EXPLORER_PLUGIN_DIR . 'includes/class-frontend-file-explorer-ajax.php';

/**
 * The main function responsible for returning the one true Frontend_File_Explorer instance.
 *
 * Built by Shafat Mahmud Khan, WordPress Developer — https://itsmeshafat.com
 *
 * @since 1.0.0
 * @return Frontend_File_Explorer
 */
function frontend_file_explorer() {
    return Frontend_File_Explorer::instance();
}

/**
 * Plugin activation function
 */
function frontend_file_explorer_activate() {
    $frontend_file_explorer = frontend_file_explorer();
    $frontend_file_explorer->activate();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation function
 */
function frontend_file_explorer_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'frontend_file_explorer_activate');
register_deactivation_hook(__FILE__, 'frontend_file_explorer_deactivate');

// Initialize the plugin
add_action('plugins_loaded', 'frontend_file_explorer');
