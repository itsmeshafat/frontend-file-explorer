<?php
/**
 * Main plugin class
 *
 * @package Frontend_File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class — crafted by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 */
class Frontend_File_Explorer {

    /**
     * The single instance of the class.
     *
     * @var Frontend_File_Explorer
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main Frontend_File_Explorer Instance.
     *
     * Ensures only one instance of Frontend_File_Explorer is loaded or can be loaded.
     *
     * @since 1.0.0
     * @static
     * @return Frontend_File_Explorer - Main instance.
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
        add_shortcode('frontend_file_explorer', array($this, 'frontend_file_explorer_shortcode'));
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
        if (!file_exists(FRONTEND_FILE_EXPLORER_UPLOADS_DIR)) {
            wp_mkdir_p(FRONTEND_FILE_EXPLORER_UPLOADS_DIR);
            
            // Create .htaccess to prevent directory listing but allow file access
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<IfModule mod_rewrite.c>\n";
            $htaccess_content .= "RewriteEngine On\n";
            $htaccess_content .= "# Only block directory listing, allow file access\n";
            $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} -d\n";
            $htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
            $htaccess_content .= "RewriteRule . - [F,L]\n";
            $htaccess_content .= "</IfModule>";
            
            file_put_contents(FRONTEND_FILE_EXPLORER_UPLOADS_DIR . '/.htaccess', $htaccess_content);
            
            // Create index.php to prevent directory listing
            file_put_contents(FRONTEND_FILE_EXPLORER_UPLOADS_DIR . '/index.php', '<?php // Silence is golden');
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
            if (get_option('frontend_file_explorer_' . $key) === false) {
                update_option('frontend_file_explorer_' . $key, $value);
            }
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('File Upload', 'frontend-file-explorer'),
            __('File Upload', 'frontend-file-explorer'),
            'upload_files',
            'frontend-file-explorer',
            array($this, 'render_admin_page'),
            'dashicons-admin-media',
            30
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        include FRONTEND_FILE_EXPLORER_PLUGIN_DIR . 'templates/frontend-file-explorer-admin.php';
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if ($hook !== 'toplevel_page_frontend-file-explorer') {
            return;
        }

        // Enqueue Material Design Icons
        wp_enqueue_style(
            'material-icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            array(),
            FRONTEND_FILE_EXPLORER_VERSION
        );

        // Enqueue plugin styles
        wp_enqueue_style(
            'frontend-file-explorer-admin-style',
            FRONTEND_FILE_EXPLORER_PLUGIN_URL . 'assets/css/frontend-file-explorer-admin.css',
            array(),
            FRONTEND_FILE_EXPLORER_VERSION
        );

        // Enqueue plugin scripts
        wp_enqueue_script(
            'frontend-file-explorer-admin-script',
            FRONTEND_FILE_EXPLORER_PLUGIN_URL . 'assets/js/frontend-file-explorer-admin.js',
            array('jquery'),
            FRONTEND_FILE_EXPLORER_VERSION,
            true
        );

        // Localize script
        wp_localize_script('frontend-file-explorer-admin-script', 'frontendFileExplorerAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('frontend_file_explorer_nonce'),
            'uploadsUrl' => FRONTEND_FILE_EXPLORER_UPLOADS_URL,
            'strings' => array(
                'confirmDelete' => __('Are you sure you want to delete this item?', 'frontend-file-explorer'),
                'createFolder' => __('Create Folder', 'frontend-file-explorer'),
                'enterFolderName' => __('Please enter a folder name', 'frontend-file-explorer'),
                'upload' => __('Upload Files', 'frontend-file-explorer'),
                'selectFiles' => __('Select Files', 'frontend-file-explorer'),
                'noFilesSelected' => __('No files selected. Please select files to upload.', 'frontend-file-explorer'),
                'loading' => __('Loading...', 'frontend-file-explorer'),
                'copySuccess' => __('Link copied to clipboard!', 'frontend-file-explorer'),
                'copyError' => __('Failed to copy link. Please try again.', 'frontend-file-explorer'),
                'error' => __('An error occurred. Please try again.', 'frontend-file-explorer'),
                'uploaded' => __('Uploaded successfully', 'frontend-file-explorer'),
                'addToFileExplorer' => __('Add to File Explorer', 'frontend-file-explorer'),
                'home' => __('Home', 'frontend-file-explorer'),
                'goBack' => __('Go Back', 'frontend-file-explorer'),
                'open' => __('Open', 'frontend-file-explorer'),
                'download' => __('Download', 'frontend-file-explorer'),
                'downloadZip' => __('Download as ZIP', 'frontend-file-explorer'),
                'delete' => __('Delete', 'frontend-file-explorer'),
                'copyLink' => __('Copy Link', 'frontend-file-explorer'),
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
        
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'frontend_file_explorer')) {
            return;
        }

        // Enqueue Material Design Icons
        wp_enqueue_style(
            'material-icons',
            'https://fonts.googleapis.com/icon?family=Material+Icons',
            array(),
            FRONTEND_FILE_EXPLORER_VERSION
        );

        // Enqueue plugin styles
        wp_enqueue_style(
            'frontend-file-explorer-frontend-style',
            FRONTEND_FILE_EXPLORER_PLUGIN_URL . 'assets/css/frontend-file-explorer.css',
            array(),
            FRONTEND_FILE_EXPLORER_VERSION
        );

        // Enqueue plugin scripts
        wp_enqueue_script(
            'frontend-file-explorer-frontend-script',
            FRONTEND_FILE_EXPLORER_PLUGIN_URL . 'assets/js/frontend-file-explorer.js',
            array('jquery'),
            FRONTEND_FILE_EXPLORER_VERSION,
            true
        );

        // Localize script
        wp_localize_script('frontend-file-explorer-frontend-script', 'frontendFileExplorerFrontend', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('frontend_file_explorer_nonce'),
            'uploadsUrl' => FRONTEND_FILE_EXPLORER_UPLOADS_URL,
            'strings' => array(
                'downloadZip' => __('Download as ZIP', 'frontend-file-explorer'),
                'open' => __('Open', 'frontend-file-explorer'),
                'download' => __('Download', 'frontend-file-explorer'),
                'copyLink' => __('Copy Link', 'frontend-file-explorer'),
                'goBack' => __('Go Back', 'frontend-file-explorer'),
                'home' => __('Home', 'frontend-file-explorer'),
                'loading' => __('Loading...', 'frontend-file-explorer'),
                'copySuccess' => __('Link copied to clipboard!', 'frontend-file-explorer'),
                'copyError' => __('Failed to copy link. Please try again.', 'frontend-file-explorer'),
                'error' => __('An error occurred. Please try again.', 'frontend-file-explorer'),
                'emptyFolder' => __('This folder is empty', 'frontend-file-explorer'),
                'loadMore' => __('Load More', 'frontend-file-explorer'),
                'loginRequired' => __('Please log in to view the file explorer.', 'frontend-file-explorer'),
                'accessDenied' => __('You do not have permission to access this content.', 'frontend-file-explorer'),
                'invalidPath' => __('Invalid folder path.', 'frontend-file-explorer'),
                'folderNotExist' => __('The specified folder does not exist.', 'frontend-file-explorer')
            )
        ));
    }

    /**
     * File explorer shortcode
     */
    public function frontend_file_explorer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'folder' => '/',
        ), $atts, 'frontend_file_explorer');

        ob_start();
        include FRONTEND_FILE_EXPLORER_PLUGIN_DIR . 'templates/frontend-file-explorer-shortcode.php';
        return ob_get_clean();
    }
}
