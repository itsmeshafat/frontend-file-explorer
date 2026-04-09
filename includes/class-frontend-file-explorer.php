<?php
/**
 * Main plugin class
 *
 * @package FFE
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class — crafted by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 */
class FFE_File_Explorer {

    /**
     * The single instance of the class.
     *
     * @var FFE_File_Explorer
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main FFE_File_Explorer Instance.
     *
     * Ensures only one instance of FFE_File_Explorer is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return FFE_File_Explorer - Main instance.
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
        
        // Note: Since WordPress 4.6, translations are automatically loaded for plugins hosted on WordPress.org
        // No need to manually load text domain
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
        add_shortcode('ffe_file_explorer', array($this, 'ffe_file_explorer_shortcode'));
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
        if (!file_exists(FFE_UPLOADS_DIR)) {
            wp_mkdir_p(FFE_UPLOADS_DIR);
            
            // Create .htaccess to prevent directory listing but allow file access
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<IfModule mod_rewrite.c>\n";
            $htaccess_content .= "RewriteEngine On\n";
            $htaccess_content .= "# Only block directory listing, allow file access\n";
            $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} -d\n";
            $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
            $htaccess_content .= "RewriteRule . - [F,L]\n";
            $htaccess_content .= "</IfModule>";
            
            file_put_contents(FFE_UPLOADS_DIR . '/.htaccess', $htaccess_content);
            
            // Create index.php to prevent directory listing
            file_put_contents(FFE_UPLOADS_DIR . '/index.php', '<?php // Silence is golden');
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
            if (get_option('ffe_' . $key) === false) {
                update_option('ffe_' . $key, $value);
            }
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('File Upload', 'ffe'),
            __('File Upload', 'ffe'),
            'upload_files',
            'ffe',
            array($this, 'render_admin_page'),
            'dashicons-admin-media',
            30
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        include FFE_PLUGIN_DIR . 'templates/ffe-file-explorer-admin.php';
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_ffe') {
            return;
        }

        // Enqueue Material Design Icons
        wp_enqueue_style(
            'material-icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            array(),
            FFE_VERSION
        );

        // Enqueue plugin styles
        wp_enqueue_style(
            'ffe-file-explorer-admin-style',
            FFE_PLUGIN_URL . 'assets/css/ffe-file-explorer-admin.css',
            array(),
            FFE_VERSION
        );

        // Enqueue plugin scripts
        wp_enqueue_script(
            'ffe-file-explorer-admin-script',
            FFE_PLUGIN_URL . 'assets/js/ffe-file-explorer-admin.js',
            array('jquery'),
            FFE_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ffe-file-explorer-admin-script', 'ffeAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ffe_nonce'),
            'uploadsUrl' => FFE_UPLOADS_URL,
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'ffe'),
                'createFolder' => __('Create Folder', 'ffe'),
                'enterFolderName' => __('Please enter a folder name', 'ffe'),
                'upload' => __('Upload Files', 'ffe'),
                'selectFiles' => __('Select Files', 'ffe'),
                'noFilesSelected' => __('No files selected. Please select files to upload.', 'ffe'),
                'loading' => __('Loading...', 'ffe'),
                'copySuccess' => __('Link copied to clipboard!', 'ffe'),
                'copyError' => __('Failed to copy link. Please try again.', 'ffe'),
                'error' => __('An error occurred. Please try again.', 'ffe'),
                'uploaded' => __('Uploaded successfully', 'ffe'),
                'addToFileExplorer' => __('Add to File Explorer', 'ffe'),
                'home' => __('Home', 'ffe'),
                'goBack' => __('Go Back', 'ffe'),
                'open' => __('Open', 'ffe'),
                'download' => __('Download', 'ffe'),
                'downloadZip' => __('Download as ZIP', 'ffe'),
                'delete' => __('Delete', 'ffe'),
                'copyLink' => __('Copy Link', 'ffe'),
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
        
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'ffe_file_explorer')) {
            return;
        }

        // Enqueue Material Design Icons
        wp_enqueue_style(
            'material-icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            array(),
            FFE_VERSION
        );

        // Enqueue plugin styles
        wp_enqueue_style(
            'ffe-file-explorer-frontend-style',
            FFE_PLUGIN_URL . 'assets/css/ffe-file-explorer.css',
            array(),
            FFE_VERSION
        );

        // Enqueue plugin scripts
        wp_enqueue_script(
            'ffe-file-explorer-frontend-script',
            FFE_PLUGIN_URL . 'assets/js/ffe-file-explorer.js',
            array('jquery'),
            FFE_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ffe-file-explorer-frontend-script', 'ffeFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ffe_nonce'),
            'uploadsUrl' => FFE_UPLOADS_URL,
            'strings' => array(
                'downloadZip' => __('Download as ZIP', 'ffe'),
                'open' => __('Open', 'ffe'),
                'download' => __('Download', 'ffe'),
                'copyLink' => __('Copy Link', 'ffe'),
                'goBack' => __('Go Back', 'ffe'),
                'home' => __('Home', 'ffe'),
                'loading' => __('Loading...', 'ffe'),
                'copySuccess' => __('Link copied to clipboard!', 'ffe'),
                'copyError' => __('Failed to copy link. Please try again.', 'ffe'),
                'error' => __('An error occurred. Please try again.', 'ffe'),
                'emptyFolder' => __('This folder is empty', 'ffe'),
                'loadMore' => __('Load More', 'ffe'),
                'loginRequired' => __('Please log in to view the file explorer.', 'ffe'),
                'accessDenied' => __('You do not have permission to access this content.', 'ffe'),
                'invalidPath' => __('Invalid folder path.', 'ffe'),
                'folderNotExist' => __('The specified folder does not exist.', 'ffe')
            )
        ));
    }

    /**
     * File explorer shortcode
     */
    public function ffe_file_explorer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'folder' => '/',
        ), $atts, 'ffe_file_explorer');

        ob_start();
        include FFE_PLUGIN_DIR . 'templates/ffe-file-explorer-shortcode.php';
        return ob_get_clean();
    }
}
