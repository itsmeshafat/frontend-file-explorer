<?php
/**
 * File Explorer AJAX Handler
 *
 * @package FrontendFileExplorer
 * @since 1.0.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * File Explorer AJAX Handler Class
 */
class FrontendFileExplorerAjax {
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
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to access this content.', 'frontend-file-explorer'));
        }
        
        $this->process_get_folder_contents();
    }

    /**
     * Frontend Get folder contents
     */
    public function frontend_get_folder_contents() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        $this->process_get_folder_contents();
    }

    private function process_get_folder_contents() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $folder_path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '/';
        
        if (strpos($folder_path, '..') !== false) {
            wp_send_json_error(__('Invalid folder path.', 'frontend-file-explorer'));
        }
        
        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']) . 'downloads';
        $base_url = trailingslashit($upload_dir['baseurl']) . 'downloads';
        
        $full_path = trailingslashit($base_dir) . ltrim($folder_path, '/');
        
        if (!file_exists($full_path) || !is_dir($full_path)) {
            wp_send_json_error(__('The specified folder does not exist.', 'frontend-file-explorer'));
        }
        
        $items = array();
        $files = scandir($full_path);
        
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === 'index.php' || $file === '.htaccess') {
                    continue;
                }
                
                $item_path = trailingslashit($full_path) . $file;
                $is_dir = is_dir($item_path);
                
                $items[] = array(
                    'name' => $file,
                    'path' => trailingslashit(rtrim($folder_path, '/')) . $file,
                    'type' => $is_dir ? 'folder' : 'file',
                    'extension' => $is_dir ? '' : strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                    'url' => $is_dir ? '' : trailingslashit($base_url) . ltrim(trailingslashit(rtrim($folder_path, '/')) . $file, '/')
                );
            }
        }
        
        wp_send_json_success(array(
            'items' => $items,
            'current_path' => $folder_path,
            'pagination' => array('has_more' => false)
        ));
    }
    
    /**
     * Create folder
     */
    public function create_folder() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        if (!current_user_can('upload_files')) wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        
        $path = isset($_POST['parent_path']) ? sanitize_text_field(wp_unslash($_POST['parent_path'])) : '/';
        $name = isset($_POST['folder_name']) ? sanitize_file_name(wp_unslash($_POST['folder_name'])) : '';
        if (empty($name) || strpos($path, '..') !== false) wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        
        $upload_dir = wp_upload_dir();
        $full_path = trailingslashit($upload_dir['basedir']) . 'downloads' . trailingslashit($path) . $name;
        
        if (file_exists($full_path)) wp_send_json_error(__('Folder already exists.', 'frontend-file-explorer'));
        if (wp_mkdir_p($full_path)) wp_send_json_success();
        
        wp_send_json_error(__('Failed to create folder.', 'frontend-file-explorer'));
    }

    /**
     * Upload files
     */
    public function upload_files() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        if (!current_user_can('upload_files')) wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        
        $path = isset($_POST['folder_path']) ? sanitize_text_field(wp_unslash($_POST['folder_path'])) : '/';
        if (strpos($path, '..') !== false) wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        if (empty($_FILES['files'])) wp_send_json_error(__('No file uploaded.', 'frontend-file-explorer'));
        
        $upload_dir = wp_upload_dir();
        $dest_dir = trailingslashit($upload_dir['basedir']) . 'downloads' . trailingslashit($path);
        wp_mkdir_p($dest_dir);
        
        require_once ABSPATH . 'wp-admin/includes/file.php';
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            WP_Filesystem();
        }
        
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
        $uploaded_files = isset($_FILES['files']) ? $_FILES['files'] : null;
        $upload_overrides = array('test_form' => false);
        
        $responses = array('files' => array(), 'errors' => array());
        
        // restructure $_FILES array for wp_handle_sideload if multiple items
        if ($uploaded_files && is_array($uploaded_files['name'])) {
            $count = count($uploaded_files['name']);
            for ($i = 0; $i < $count; $i++) {
                $file = array(
                    'name' => $uploaded_files['name'][$i],
                    'type' => $uploaded_files['type'][$i],
                    'tmp_name' => $uploaded_files['tmp_name'][$i],
                    'error' => $uploaded_files['error'][$i],
                    'size' => $uploaded_files['size'][$i]
                );
                
                $movefile = wp_handle_sideload($file, $upload_overrides);
                if ($movefile && !isset($movefile['error'])) {
                    $filename = wp_basename($movefile['file']);
                    $new_file = trailingslashit($dest_dir) . $filename;
                    $wp_filesystem->move($movefile['file'], $new_file);
                    $responses['files'][] = array('name' => $file['name']);
                } else {
                    $responses['errors'][] = 'Error uploading ' . $file['name'] . ': ' . $movefile['error'];
                }
            }
        }
        
        if (empty($responses['errors'])) {
            wp_send_json_success($responses);
        } else {
            wp_send_json_error($responses);
        }
    }

    /**
     * Add media files
     */
    public function add_media_files() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        if (!current_user_can('upload_files')) wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        
        $path = isset($_POST['folder_path']) ? sanitize_text_field(wp_unslash($_POST['folder_path'])) : '/';
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $attachment_ids = isset($_POST['media_ids']) ? array_map('intval', wp_unslash((array) $_POST['media_ids'])) : array();
        if (empty($attachment_ids) || strpos($path, '..') !== false) wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        
        $upload_dir = wp_upload_dir();
        $dest_dir = trailingslashit($upload_dir['basedir']) . 'downloads' . trailingslashit($path);
        wp_mkdir_p($dest_dir);
        
        foreach ($attachment_ids as $id) {
            $file_path = get_attached_file(intval($id));
            if ($file_path && file_exists($file_path)) {
                copy($file_path, trailingslashit($dest_dir) . wp_basename($file_path));
            }
        }
        wp_send_json_success();
    }

    /**
     * Delete item
     */
    public function delete_item() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        if (!current_user_can('upload_files')) wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        
        $path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';
        $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
        if (empty($path) || strpos($path, '..') !== false) wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        
        $upload_dir = wp_upload_dir();
        $full_path = trailingslashit($upload_dir['basedir']) . 'downloads' . trailingslashit($path);
        
        if (!file_exists($full_path)) wp_send_json_error(__('Item does not exist.', 'frontend-file-explorer'));
        
        $success = false;
        if ($type === 'folder' || is_dir($full_path)) {
            $success = $this->delete_directory($full_path);
        } else {
            $success = wp_delete_file($full_path);
        }
        
        if ($success) wp_send_json_success(__('Item deleted successfully.', 'frontend-file-explorer'));
        wp_send_json_error(__('Failed to delete item.', 'frontend-file-explorer'));
    }

    /**
     * Download as ZIP
     */
    public function download_as_zip() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $folder_path = isset($_REQUEST['path']) ? sanitize_text_field(wp_unslash($_REQUEST['path'])) : '';
        
        if (empty($folder_path)) {
            wp_die(esc_html__('No path specified.', 'frontend-file-explorer'));
        }
        
        if (strpos($folder_path, '..') !== false) {
            wp_die(esc_html__('Invalid path.', 'frontend-file-explorer'));
        }
        
        // Get uploads directory explicitly
        $upload_dir = wp_upload_dir();
        $basedir = $upload_dir['basedir'];
        
        // Build path - use trailingslashit for consistency
        $base_dir = trailingslashit($basedir) . 'downloads';
        $full_path = trailingslashit($base_dir) . ltrim($folder_path, '/');
        
        if (!file_exists($full_path)) {
            /* translators: %s: File or folder path that could not be found */
            wp_die(sprintf(esc_html__('Path does not exist: %s', 'frontend-file-explorer'), esc_html($full_path)));
        }
        
        // Generate a unique temp file path without pre-creating it on disk
        $temp_file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ffe_' . uniqid('zip_') . '.tmp';
        
        $zip = new ZipArchive();
        if ($zip->open($temp_file, ZipArchive::CREATE) !== true) {
            wp_die(esc_html__('Could not create ZIP file.', 'frontend-file-explorer'));
        }
        
        if (is_dir($full_path)) {
            $base_name = basename($full_path);
            $this->add_dir_to_zip($zip, $full_path, $base_name);
        } else {
            $zip->addFile($full_path, basename($full_path));
        }
        
        $zip->close();
        
        // Clean output buffer to avoid ERR_INVALID_RESPONSE and corrupted zips
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $download_filename = basename($folder_path);
        if (empty($download_filename)) {
            $download_filename = 'folder';
        }
        
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="' . $download_filename . '.zip"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($temp_file));
        header('Cache-Control: private, no-transform, no-store, must-revalidate');
        
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
        readfile($temp_file);
        
        // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
        @unlink($temp_file);
        
        exit;
    }
    
    /**
     * Get file link
     */
    public function get_file_link() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        
        $path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';
        
        if (empty($path)) {
            wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        }
        
        if (strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }
        
        $uploads_dir = wp_upload_dir();
        $base_dir = $uploads_dir['basedir'] . '/downloads';
        $full_path = $base_dir . $path;
        
        if (!file_exists($full_path) || is_dir($full_path)) {
            wp_send_json_error(__('The specified file does not exist.', 'frontend-file-explorer'));
        }
        
        $url = $uploads_dir['baseurl'] . '/downloads' . $path;
        
        wp_send_json_success($url);
    }
    
    /**
     * Delete directory recursively
     */
    private function delete_directory($dir) {
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
     * Add directory to ZIP
     */
    private function add_dir_to_zip($zip, $dir, $base_dir) {
        $new_dir = $base_dir;
        
        $zip->addEmptyDir($new_dir);
        
        foreach (scandir($dir) as $file) {
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