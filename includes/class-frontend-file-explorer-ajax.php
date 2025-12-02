<?php
/**
 * File Explorer AJAX Handler
 *
 * @package Frontend_File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * File Explorer AJAX Handler Class — built by Shafat Mahmud Khan (WordPress Developer, https://itsmeshafat.com)
 */
class Frontend_File_Explorer_Ajax {
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin AJAX handlers
        add_action('wp_ajax_frontend_file_explorer_get_folder_contents', array($this, 'get_folder_contents'));
        add_action('wp_ajax_frontend_file_explorer_create_folder', array($this, 'create_folder'));
        add_action('wp_ajax_frontend_file_explorer_upload_files', array($this, 'upload_files'));
        add_action('wp_ajax_frontend_file_explorer_add_media_files', array($this, 'add_media_files'));
        add_action('wp_ajax_frontend_file_explorer_delete_item', array($this, 'delete_item'));
        add_action('wp_ajax_frontend_file_explorer_download_as_zip', array($this, 'download_as_zip'));
        add_action('wp_ajax_frontend_file_explorer_get_file_link', array($this, 'get_file_link'));
        
        // Frontend AJAX handlers
        add_action('wp_ajax_frontend_file_explorer_frontend_get_folder_contents', array($this, 'frontend_get_folder_contents'));
        add_action('wp_ajax_nopriv_frontend_file_explorer_frontend_get_folder_contents', array($this, 'frontend_get_folder_contents'));
        add_action('wp_ajax_frontend_file_explorer_frontend_download_as_zip', array($this, 'download_as_zip'));
        add_action('wp_ajax_nopriv_frontend_file_explorer_frontend_download_as_zip', array($this, 'download_as_zip'));
        add_action('wp_ajax_frontend_file_explorer_frontend_get_file_link', array($this, 'get_file_link'));
        add_action('wp_ajax_nopriv_frontend_file_explorer_frontend_get_file_link', array($this, 'get_file_link'));
    }
    
    /**
     * Get folder contents
     */
    public function get_folder_contents() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to access this content.', 'frontend-file-explorer'));
        }
        
        $folder_path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '/';
        
        // Validate folder path
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $server_path = $base_dir . $folder_path;
        
        // Check if the path exists and is a directory
        if (!file_exists($server_path) || !is_dir($server_path)) {
            wp_send_json_error(__('The specified folder does not exist.', 'frontend-file-explorer'));
        }
        
        // Get folder contents
        $folders = array();
        $files = array();
        
        if ($handle = opendir($server_path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != ".htaccess" && $entry != "index.php") {
                    $full_path = $server_path . '/' . $entry;
                    $relative_path = $folder_path . '/' . $entry;
                    $relative_path = str_replace('//', '/', $relative_path);
                    
                    if (is_dir($full_path)) {
                        $folders[] = array(
                            'name' => $entry,
                            'path' => $relative_path,
                            'type' => 'folder'
                        );
                    } else {
                        $file_type = wp_check_filetype($entry);
                        $files[] = array(
                            'name' => $entry,
                            'path' => $relative_path,
                            'type' => 'file',
                            'extension' => $file_type['ext'],
                            'url' => $uploads_dir['baseurl'] . '/downloads' . $relative_path
                        );
                    }
                }
            }
            closedir($handle);
        }
        
        // Sort folders and files
        usort($folders, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        usort($files, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        // Pagination
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $items_per_page = get_option('frontend_file_explorer_items_per_page', 20);
        
        $all_items = array_merge($folders, $files);
        $total_items = count($all_items);
        $total_pages = ceil($total_items / $items_per_page);
        
        $offset = ($page - 1) * $items_per_page;
        $items = array_slice($all_items, $offset, $items_per_page);
        
        // Prepare response
        $response = array(
            'items' => $items,
            'current_path' => $folder_path,
            'pagination' => array(
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total_items,
                'has_more' => ($page < $total_pages)
            )
        );
        
        wp_send_json_success($response);
    }
    
    /**
     * Frontend get folder contents
     */
    public function frontend_get_folder_contents() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        // We allow all users to view the content in frontend
        
        $folder_path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '/';
        
        // Validate folder path
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $server_path = $base_dir . $folder_path;
        
        // Check if the path exists and is a directory
        if (!file_exists($server_path) || !is_dir($server_path)) {
            wp_send_json_error(__('The specified folder does not exist.', 'frontend-file-explorer'));
        }
        
        // Get folder contents
        $folders = array();
        $files = array();
        
        if ($handle = opendir($server_path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != ".htaccess" && $entry != "index.php") {
                    $full_path = $server_path . '/' . $entry;
                    $relative_path = $folder_path . '/' . $entry;
                    $relative_path = str_replace('//', '/', $relative_path);
                    
                    if (is_dir($full_path)) {
                        $folders[] = array(
                            'name' => $entry,
                            'path' => $relative_path,
                            'type' => 'folder'
                        );
                    } else {
                        $file_type = wp_check_filetype($entry);
                        $files[] = array(
                            'name' => $entry,
                            'path' => $relative_path,
                            'type' => 'file',
                            'extension' => $file_type['ext'],
                            'url' => $uploads_dir['baseurl'] . '/downloads' . $relative_path
                        );
                    }
                }
            }
            closedir($handle);
        }
        
        // Sort folders and files
        usort($folders, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        usort($files, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        // Pagination for frontend
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $items_per_page = get_option('frontend_file_explorer_items_per_page', 20);
        
        $all_items = array_merge($folders, $files);
        $total_items = count($all_items);
        $total_pages = ceil($total_items / $items_per_page);
        
        $offset = ($page - 1) * $items_per_page;
        $items = array_slice($all_items, $offset, $items_per_page);
        
        // Prepare response
        $response = array(
            'items' => $items,
            'current_path' => $folder_path,
            'pagination' => array(
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_items' => $total_items,
                'has_more' => ($page < $total_pages)
            )
        );
        
        wp_send_json_success($response);
    }
    
    /**
     * Create folder
     */
    public function create_folder() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to create folders.', 'frontend-file-explorer'));
        }
        
        $folder_name = isset($_POST['folder_name']) ? sanitize_file_name(wp_unslash($_POST['folder_name'])) : '';
        $parent_path = isset($_POST['parent_path']) ? sanitize_text_field(wp_unslash($_POST['parent_path'])) : '/';
        
        if (empty($folder_name)) {
            wp_send_json_error(__('Please enter a folder name.', 'frontend-file-explorer'));
        }
        
        // Validate parent path
        if (strpos($parent_path, '..') !== false) {
            wp_send_json_error(__('Invalid parent path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $parent_dir = $base_dir . $parent_path;
        $new_folder = $parent_dir . '/' . $folder_name;
        
        // Check if the parent directory exists
        if (!file_exists($parent_dir) || !is_dir($parent_dir)) {
            wp_send_json_error(__('The parent directory does not exist.', 'frontend-file-explorer'));
        }
        
        // Check if the folder already exists
        if (file_exists($new_folder)) {
            wp_send_json_error(__('A folder with this name already exists.', 'frontend-file-explorer'));
        }
        
        // Create the folder
        if (wp_mkdir_p($new_folder)) {
            wp_send_json_success(__('Folder created successfully.', 'frontend-file-explorer'));
        } else {
            wp_send_json_error(__('Failed to create folder.', 'frontend-file-explorer'));
        }
    }
    
    /**
     * Upload files
     */
    public function upload_files() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to upload files.', 'frontend-file-explorer'));
        }
        
        $folder_path = isset($_POST['folder_path']) ? sanitize_text_field(wp_unslash($_POST['folder_path'])) : '/';
        
        // Validate folder path
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $target_dir = $uploads_dir['basedir'] . '/downloads' . $folder_path;
        
        // Check if the target directory exists
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }
        
        // Validate and sanitize $_FILES array
        if ( ! isset( $_FILES['files'] ) || ! is_array( $_FILES['files'] ) ) {
            wp_send_json_error( __( 'No files were uploaded.', 'frontend-file-explorer' ) );
        }
        
        // Sanitize the files array
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization is performed on individual file properties (name, etc.) in the loop below.
        $files = wp_unslash( $_FILES['files'] );
        
        // Ensure the required keys exist in the files array
        if (!isset($files['name'], $files['tmp_name'], $files['error']) || !is_array($files['name'])) {
            wp_send_json_error(__('Invalid file upload data.', 'frontend-file-explorer'));
        }
        
        $uploaded_files = array();
        
        // Loop through each file
        for ($i = 0; $i < count($files['name']); $i++) {
            $file_name = isset($files['name'][$i]) ? sanitize_file_name($files['name'][$i]) : '';
            $file_tmp = isset($files['tmp_name'][$i]) ? $files['tmp_name'][$i] : '';
            $file_error = isset($files['error'][$i]) ? $files['error'][$i] : UPLOAD_ERR_NO_FILE;
            
            // Check for errors
            if ($file_error !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Move the file to the target directory
            $target_file = $target_dir . '/' . $file_name;
            
            // Initialize WordPress filesystem
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once(ABSPATH . '/wp-admin/includes/file.php');
                WP_Filesystem();
            }
            
            if ($wp_filesystem->move_uploaded_file($file_tmp, $target_file)) {
                $uploaded_files[] = $file_name;
            }
        }
        
        if (count($uploaded_files) > 0) {
            wp_send_json_success(array(
                'message' => sprintf(
                    /* translators: %d: Number of files uploaded */
                    __('%d files uploaded successfully.', 'frontend-file-explorer'), 
                    count($uploaded_files)
                ),
                'files' => $uploaded_files
            ));
        } else {
            wp_send_json_error(__('Failed to upload files.', 'frontend-file-explorer'));
        }
    }
    
    /**
     * Add media files to folder (secure version using attachment IDs)
     */
    public function add_media_files() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to copy files.', 'frontend-file-explorer'));
        }
        
        // Get parameters
        $folder_path = isset($_POST['folder_path']) ? sanitize_text_field(wp_unslash($_POST['folder_path'])) : '/';
        $media_ids = isset($_POST['media_ids']) ? array_map('absint', wp_unslash($_POST['media_ids'])) : array();
        
        // Validate media IDs
        if (!is_array($media_ids) || empty($media_ids)) {
            wp_send_json_error(__('Invalid media IDs.', 'frontend-file-explorer'));
        }
        
        // Sanitize media IDs to integers
        $media_ids = array_map('absint', $media_ids);
        
        // Validate folder path
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Create the target directory if it doesn't exist
        $target_dir = $base_dir . $folder_path;
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }
        
        // Copy each file using attachment IDs
        $copied_files = array();
        foreach ($media_ids as $attachment_id) {
            // Verify this is a valid attachment
            if (get_post_type($attachment_id) !== 'attachment') {
                continue;
            }
            
            // Get the file path securely using WordPress function
            $file_path = get_attached_file($attachment_id);
            
            if ($file_path && file_exists($file_path)) {
                // Get the filename
                $file_name = basename($file_path);
                $file_name = sanitize_file_name($file_name);
                
                // Generate target path
                $target_path = $target_dir . '/' . $file_name;
                
                // Copy the file
                if (copy($file_path, $target_path)) {
                    $copied_files[] = $file_name;
                }
            }
        }
        
        if (count($copied_files) > 0) {
            wp_send_json_success(sprintf(
                /* translators: %d: Number of files copied */
                __('%d files copied successfully.', 'frontend-file-explorer'),
                count($copied_files)
            ));
        } else {
            wp_send_json_error(__('No files were copied.', 'frontend-file-explorer'));
        }
    }
    
    /**
     * Delete item
     */
    public function delete_item() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to delete files or folders.', 'frontend-file-explorer'));
        }
        
        $path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';
        $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
        
        if (empty($path) || empty($type)) {
            wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        }
        
        // Validate path
        if (strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $full_path = $base_dir . $path;
        
        // Check if the path exists
        if (!file_exists($full_path)) {
            wp_send_json_error(__('The specified item does not exist.', 'frontend-file-explorer'));
        }
        
        // Delete the item
        $success = false;
        if ($type === 'folder') {
            // Delete folder recursively
            $success = $this->delete_directory($full_path);
        } else {
            // Delete file using WordPress function
            $success = wp_delete_file($full_path);
        }
        
        if ($success) {
            wp_send_json_success(__('Item deleted successfully.', 'frontend-file-explorer'));
        } else {
            wp_send_json_error(__('Failed to delete item.', 'frontend-file-explorer'));
        }
    }
    
    /**
     * Download as ZIP
     */
    public function download_as_zip() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        // For frontend, we allow all users to download
        // No permission check needed for downloading
        
        $folder_path = isset($_REQUEST['path']) ? sanitize_text_field(wp_unslash($_REQUEST['path'])) : '';
        
        // Validate path
        if (strpos($folder_path, '..') !== false) {
            wp_die(esc_html__('Invalid path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $full_path = $base_dir . $folder_path;
        
        // Check if the path exists
        if (!file_exists($full_path)) {
            wp_die(esc_html__('The specified item does not exist.', 'frontend-file-explorer'));
        }
        
        // Create a temporary file for the ZIP
        $temp_file = tempnam(sys_get_temp_dir(), 'zip');
        
        // Create ZIP file
        $zip = new ZipArchive();
        if ($zip->open($temp_file, ZipArchive::CREATE) !== true) {
            wp_die(esc_html__('Could not create ZIP file.', 'frontend-file-explorer'));
        }
        
        // Add files to the ZIP
        if (is_dir($full_path)) {
            // Add directory contents
            $base_name = basename($full_path);
            $this->add_dir_to_zip($zip, $full_path, $base_name);
        } else {
            // Add single file
            $zip->addFile($full_path, basename($full_path));
        }
        
        $zip->close();
        
        // Set headers for download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($folder_path) . '.zip"');
        header('Content-Length: ' . filesize($temp_file));
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Initialize WordPress filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        // Output the file
        $wp_filesystem->readfile($temp_file);
        
        // Delete the temporary file
        wp_delete_file($temp_file);
        
        exit;
    }
    
    /**
     * Get file link
     */
    public function get_file_link() {
        // Check nonce
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        $path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';
        
        if (empty($path)) {
            wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        }
        
        // Validate path
        if (strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $full_path = $base_dir . $path;
        
        // Check if the file exists
        if (!file_exists($full_path) || is_dir($full_path)) {
            wp_send_json_error(__('The specified file does not exist.', 'frontend-file-explorer'));
        }
        
        // Generate the URL
        $url = $uploads_dir['baseurl'] . '/downloads' . $path;
        
        wp_send_json_success($url);
    }
    
    /**
     * Helper function to delete directory recursively
     */
    private function delete_directory($dir) {
        // Initialize WordPress filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        if (!$wp_filesystem->exists($dir)) {
            return true;
        }
        
        if (!$wp_filesystem->is_dir($dir)) {
            return wp_delete_file($dir);
        }
        
        $files = $wp_filesystem->dirlist($dir);
        if (empty($files)) {
            return $wp_filesystem->rmdir($dir);
        }
        
        foreach ($files as $file => $fileinfo) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $full_path = $dir . DIRECTORY_SEPARATOR . $file;
            if ($fileinfo['type'] === 'd') {
                $this->delete_directory($full_path);
            } else {
                wp_delete_file($full_path);
            }
        }
        
        return $wp_filesystem->rmdir($dir);
    }
    
    /**
     * Helper function to add directory to ZIP
     */
    private function add_dir_to_zip($zip, $dir, $base_dir) {
        $new_dir = $base_dir;
        
        $zip->addEmptyDir($new_dir);
        
        foreach (scandir($dir) as $file) {
            // Skip . and .. directories and index.php files
            if ($file == '.' || $file == '..' || $file == 'index.php' || $file == '.htaccess') {
                continue;
            }
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $this->add_dir_to_zip($zip, $path, $new_dir . '/' . $file);
            } else {
                $zip->addFile($path, $new_dir . '/' . $file);
            }
        }
    }
}

// Initialize the AJAX handler
new Frontend_File_Explorer_Ajax();
