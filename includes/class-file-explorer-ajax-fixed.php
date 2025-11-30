<?php
/**
 * File Explorer AJAX Handler
 *
 * @package File_Explorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * File Explorer AJAX Handler Class
 */
class File_Explorer_Ajax {
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
        add_action('wp_ajax_file_explorer_get_folder_contents', array($this, 'get_folder_contents'));
        add_action('wp_ajax_file_explorer_create_folder', array($this, 'create_folder'));
        add_action('wp_ajax_file_explorer_upload_files', array($this, 'upload_files'));
        add_action('wp_ajax_file_explorer_copy_media_to_folder', array($this, 'copy_media_to_folder'));
        add_action('wp_ajax_file_explorer_delete_item', array($this, 'delete_item'));
        add_action('wp_ajax_file_explorer_download_as_zip', array($this, 'download_as_zip'));
        add_action('wp_ajax_file_explorer_get_file_link', array($this, 'get_file_link'));
        
        // Frontend AJAX handlers
        add_action('wp_ajax_file_explorer_frontend_get_folder_contents', array($this, 'frontend_get_folder_contents'));
        add_action('wp_ajax_nopriv_file_explorer_frontend_get_folder_contents', array($this, 'frontend_get_folder_contents'));
        add_action('wp_ajax_file_explorer_frontend_download_as_zip', array($this, 'download_as_zip'));
        add_action('wp_ajax_nopriv_file_explorer_frontend_download_as_zip', array($this, 'download_as_zip'));
    }
    
    /**
     * Get folder contents
     */
    public function get_folder_contents() {
        // Check nonce
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to access this content.', 'file-explorer'));
        }
        
        $folder_path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '/';
        
        // Validate folder path
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $server_path = $base_dir . $folder_path;
        
        // Check if the path exists and is a directory
        if (!file_exists($server_path) || !is_dir($server_path)) {
            wp_send_json_error(__('The specified folder does not exist.', 'file-explorer'));
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
        $items_per_page = get_option('file_explorer_items_per_page', 20);
        
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
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        // We allow all users to view the content in frontend
        
        $folder_path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '/';
        
        // Validate folder path
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $server_path = $base_dir . $folder_path;
        
        // Check if the path exists and is a directory
        if (!file_exists($server_path) || !is_dir($server_path)) {
            wp_send_json_error(__('The specified folder does not exist.', 'file-explorer'));
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
        $items_per_page = get_option('file_explorer_items_per_page', 20);
        
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
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to create folders.', 'file-explorer'));
        }
        
        $folder_name = isset($_POST['folder_name']) ? sanitize_file_name($_POST['folder_name']) : '';
        $parent_path = isset($_POST['parent_path']) ? sanitize_text_field($_POST['parent_path']) : '/';
        
        if (empty($folder_name)) {
            wp_send_json_error(__('Please enter a folder name.', 'file-explorer'));
        }
        
        // Validate parent path
        if (strpos($parent_path, '..') !== false) {
            wp_send_json_error(__('Invalid parent path.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $parent_dir = $base_dir . $parent_path;
        $new_folder = $parent_dir . '/' . $folder_name;
        
        // Check if the parent directory exists
        if (!file_exists($parent_dir) || !is_dir($parent_dir)) {
            wp_send_json_error(__('The parent directory does not exist.', 'file-explorer'));
        }
        
        // Check if the folder already exists
        if (file_exists($new_folder)) {
            wp_send_json_error(__('A folder with this name already exists.', 'file-explorer'));
        }
        
        // Create the folder
        if (wp_mkdir_p($new_folder)) {
            wp_send_json_success(__('Folder created successfully.', 'file-explorer'));
        } else {
            wp_send_json_error(__('Failed to create folder.', 'file-explorer'));
        }
    }
    
    /**
     * Upload files
     */
    public function upload_files() {
        // Check nonce
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to upload files.', 'file-explorer'));
        }
        
        $folder_path = isset($_POST['folder_path']) ? sanitize_text_field($_POST['folder_path']) : '/';
        
        // Validate folder path
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $target_dir = $uploads_dir['basedir'] . '/downloads' . $folder_path;
        
        // Check if the target directory exists
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }
        
        // Check if files were uploaded
        if (empty($_FILES['files'])) {
            wp_send_json_error(__('No files were uploaded.', 'file-explorer'));
        }
        
        $files = $_FILES['files'];
        $uploaded_files = array();
        
        // Loop through each file
        for ($i = 0; $i < count($files['name']); $i++) {
            $file_name = sanitize_file_name($files['name'][$i]);
            $file_tmp = $files['tmp_name'][$i];
            $file_error = $files['error'][$i];
            
            // Check for errors
            if ($file_error !== UPLOAD_ERR_OK) {
                continue;
            }
            
            // Move the file to the target directory
            $target_file = $target_dir . '/' . $file_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $uploaded_files[] = $file_name;
            }
        }
        
        if (count($uploaded_files) > 0) {
            wp_send_json_success(array(
                'message' => sprintf(__('%d files uploaded successfully.', 'file-explorer'), count($uploaded_files)),
                'files' => $uploaded_files
            ));
        } else {
            wp_send_json_error(__('Failed to upload files.', 'file-explorer'));
        }
    }
    
    /**
     * Copy media files to folder
     */
    public function copy_media_to_folder() {
        // Check nonce
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to copy files.', 'file-explorer'));
        }
        
        // Get parameters
        $folder_path = isset($_POST['folder_path']) ? sanitize_text_field($_POST['folder_path']) : '/';
        $files_json = isset($_POST['files']) ? stripslashes($_POST['files']) : '';
        
        // Decode files JSON
        $files = json_decode($files_json, true);
        if (!$files || !is_array($files)) {
            wp_send_json_error(__('Invalid files data.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Create the target directory if it doesn't exist
        $target_dir = $base_dir . $folder_path;
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }
        
        // Copy each file
        $copied_files = array();
        foreach ($files as $file) {
            // Get the file URL and convert to path
            $file_url = $file['url'];
            $file_name = sanitize_file_name($file['name']);
            
            // Get the file path from the URL
            $file_path = str_replace($uploads_dir['baseurl'], $uploads_dir['basedir'], $file_url);
            
            if ($file_path && file_exists($file_path)) {
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
                __('%d files copied successfully.', 'file-explorer'),
                count($copied_files)
            ));
        } else {
            wp_send_json_error(__('No files were copied.', 'file-explorer'));
        }
    }
    
    /**
     * Delete item
     */
    public function delete_item() {
        // Check nonce
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to delete files or folders.', 'file-explorer'));
        }
        
        $path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        
        if (empty($path) || empty($type)) {
            wp_send_json_error(__('Invalid request.', 'file-explorer'));
        }
        
        // Validate path
        if (strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $full_path = $base_dir . $path;
        
        // Check if the path exists
        if (!file_exists($full_path)) {
            wp_send_json_error(__('The specified item does not exist.', 'file-explorer'));
        }
        
        // Delete the item
        $success = false;
        if ($type === 'folder') {
            // Delete folder recursively
            $success = $this->delete_directory($full_path);
        } else {
            // Delete file
            $success = unlink($full_path);
        }
        
        if ($success) {
            wp_send_json_success(__('Item deleted successfully.', 'file-explorer'));
        } else {
            wp_send_json_error(__('Failed to delete item.', 'file-explorer'));
        }
    }
    
    /**
     * Download as ZIP
     */
    public function download_as_zip() {
        // Check nonce
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        // For frontend, we allow all users to download
        // No permission check needed for downloading
        
        $folder_path = isset($_REQUEST['path']) ? sanitize_text_field($_REQUEST['path']) : '';
        
        // Validate path
        if (strpos($folder_path, '..') !== false) {
            wp_die(__('Invalid path.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $full_path = $base_dir . $folder_path;
        
        // Check if the path exists
        if (!file_exists($full_path)) {
            wp_die(__('The specified item does not exist.', 'file-explorer'));
        }
        
        // Create a temporary file for the ZIP
        $temp_file = tempnam(sys_get_temp_dir(), 'zip');
        
        // Create ZIP file
        $zip = new ZipArchive();
        if ($zip->open($temp_file, ZipArchive::CREATE) !== true) {
            wp_die(__('Could not create ZIP file.', 'file-explorer'));
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
        
        // Output the file
        readfile($temp_file);
        
        // Delete the temporary file
        unlink($temp_file);
        
        exit;
    }
    
    /**
     * Get file link
     */
    public function get_file_link() {
        // Check nonce
        check_ajax_referer('file_explorer_nonce', 'nonce');
        
        $path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '';
        
        if (empty($path)) {
            wp_send_json_error(__('Invalid request.', 'file-explorer'));
        }
        
        // Validate path
        if (strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'file-explorer'));
        }
        
        // Get the uploads directory
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        
        // Build the full server path
        $full_path = $base_dir . $path;
        
        // Check if the file exists
        if (!file_exists($full_path) || is_dir($full_path)) {
            wp_send_json_error(__('The specified file does not exist.', 'file-explorer'));
        }
        
        // Generate the URL
        $url = $uploads_dir['baseurl'] . '/downloads' . $path;
        
        wp_send_json_success($url);
    }
    
    /**
     * Helper function to delete directory recursively
     */
    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!$this->delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Helper function to add directory to ZIP
     */
    private function add_dir_to_zip($zip, $dir, $base_dir) {
        $new_dir = $base_dir;
        
        $zip->addEmptyDir($new_dir);
        
        foreach (scandir($dir) as $file) {
            if ($file == '.' || $file == '..') {
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
new File_Explorer_Ajax();
