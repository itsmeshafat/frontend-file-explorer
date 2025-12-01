<?php
/**
 * Main plugin class
 *
 * @package File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class — crafted by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 */
class File_Explorer {

    /**
     * The single instance of the class.
     *
     * @var File_Explorer
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main File_Explorer Instance.
     *
     * Ensures only one instance of File_Explorer is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return File_Explorer - Main instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->init_hooks();
        
        // Load plugin text domain
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
    }

    /**
     * Define constants
     */
    private function define_constants() {
        // No additional constants needed here as they're defined in the main plugin file
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        
        // Register shortcode
        add_shortcode('file_explorer', array($this, 'file_explorer_shortcode'));
    }

    /**
     * Load plugin text domain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('frontend-file-explorer-plugin', false, dirname(FILE_EXPLORER_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create downloads directory
        $this->create_downloads_directory();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create downloads directory
     */
    private function create_downloads_directory() {
        if (!file_exists(FILE_EXPLORER_UPLOADS_DIR)) {
            wp_mkdir_p(FILE_EXPLORER_UPLOADS_DIR);
            
            // Create .htaccess to prevent directory listing but allow file access
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<IfModule mod_rewrite.c>\n";
            $htaccess_content .= "RewriteEngine On\n";
            $htaccess_content .= "# Only block directory listing, allow file access\n";
            $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} -d\n";
            $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
            $htaccess_content .= "RewriteRule . - [F,L]\n";
            $htaccess_content .= "</IfModule>";
            
            file_put_contents(FILE_EXPLORER_UPLOADS_DIR . '/.htaccess', $htaccess_content);
            
            // Create index.php to prevent directory listing
            file_put_contents(FILE_EXPLORER_UPLOADS_DIR . '/index.php', '<?php // Silence is golden');
        }
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        $default_options = array(
            'allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar,txt',
            'max_upload_size' => wp_max_upload_size(),
            'items_per_page' => 20
        );

        foreach ($default_options as $key => $value) {
            if (get_option('file_explorer_' . $key) === false) {
                update_option('file_explorer_' . $key, $value);
            }
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('File Upload', 'file-explorer'),
            __('File Upload', 'file-explorer'),
            'upload_files',
            'file-explorer',
            array($this, 'render_admin_page'),
            'dashicons-admin-media',
            30
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        include FILE_EXPLORER_PLUGIN_DIR . 'templates/admin-interface.php';
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_file-explorer') {
            return;
        }

        // Enqueue Material Design Icons
        wp_enqueue_style(
            'material-icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            array(),
            FILE_EXPLORER_VERSION
        );

        // Enqueue plugin styles
        wp_enqueue_style(
            'file-explorer-admin-style',
            FILE_EXPLORER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            FILE_EXPLORER_VERSION
        );

        // Enqueue plugin scripts
        wp_enqueue_script(
            'file-explorer-admin-script',
            FILE_EXPLORER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            FILE_EXPLORER_VERSION,
            true
        );

        // Localize script
        wp_localize_script('file-explorer-admin-script', 'fileExplorerAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('file_explorer_nonce'),
            'uploadsUrl' => FILE_EXPLORER_UPLOADS_URL,
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'file-explorer'),
                'createFolder' => __('Create Folder', 'file-explorer'),
                'enterFolderName' => __('Please enter a folder name', 'file-explorer'),
                'upload' => __('Upload Files', 'file-explorer'),
                'selectFiles' => __('Select Files', 'file-explorer'),
                'noFilesSelected' => __('No files selected. Please select files to upload.', 'file-explorer'),
                'loading' => __('Loading...', 'file-explorer'),
                'copySuccess' => __('Link copied to clipboard!', 'file-explorer'),
                'copyError' => __('Failed to copy link. Please try again.', 'file-explorer'),
                'error' => __('An error occurred. Please try again.', 'file-explorer'),
                'uploaded' => __('Uploaded successfully', 'file-explorer'),
                'addToFileExplorer' => __('Add to File Explorer', 'file-explorer'),
                'home' => __('Home', 'file-explorer'),
                'goBack' => __('Go Back', 'file-explorer'),
                'open' => __('Open', 'file-explorer'),
                'download' => __('Download', 'file-explorer'),
                'downloadZip' => __('Download as ZIP', 'file-explorer'),
                'delete' => __('Delete', 'file-explorer'),
                'copyLink' => __('Copy Link', 'file-explorer'),
            )
        ));

        // Enqueue WordPress Media Uploader
        wp_enqueue_media();
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        global $post;
        
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'file_explorer')) {
            return;
        }

        // Enqueue Material Design Icons
        wp_enqueue_style(
            'material-icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            array(),
            FILE_EXPLORER_VERSION
        );

        // Enqueue plugin styles
        wp_enqueue_style(
            'file-explorer-frontend-style',
            FILE_EXPLORER_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            FILE_EXPLORER_VERSION
        );

        // Enqueue plugin scripts
        wp_enqueue_script(
            'file-explorer-frontend-script',
            FILE_EXPLORER_PLUGIN_URL . 'assets/js/frontend.js',
            array('jquery'),
            FILE_EXPLORER_VERSION,
            true
        );

        // Localize script
        wp_localize_script('file-explorer-frontend-script', 'fileExplorerFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('file_explorer_nonce'),
            'uploadsUrl' => FILE_EXPLORER_UPLOADS_URL,
            'strings' => array(
                'downloadZip' => __('Download as ZIP', 'file-explorer'),
                'open' => __('Open', 'file-explorer'),
                'download' => __('Download', 'file-explorer'),
                'copyLink' => __('Copy Link', 'file-explorer'),
                'goBack' => __('Go Back', 'file-explorer'),
                'home' => __('Home', 'file-explorer'),
                'loading' => __('Loading...', 'file-explorer'),
                'copySuccess' => __('Link copied to clipboard!', 'file-explorer'),
                'copyError' => __('Failed to copy link. Please try again.', 'file-explorer'),
                'error' => __('An error occurred. Please try again.', 'file-explorer'),
                'emptyFolder' => __('This folder is empty', 'file-explorer'),
                'loadMore' => __('Load More', 'file-explorer'),
                'loginRequired' => __('Please log in to view the file explorer.', 'file-explorer'),
                'accessDenied' => __('You do not have permission to access this content.', 'file-explorer'),
                'invalidPath' => __('Invalid folder path.', 'file-explorer'),
                'folderNotExist' => __('The specified folder does not exist.', 'file-explorer')
            )
        ));
    }

    /**
     * File explorer shortcode
     */
    public function file_explorer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'folder' => '/',
        ), $atts, 'file_explorer');

        ob_start();
        include FILE_EXPLORER_PLUGIN_DIR . 'templates/frontend-interface.php';
        return ob_get_clean();
    }
}
