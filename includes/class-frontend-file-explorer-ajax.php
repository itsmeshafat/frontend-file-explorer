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
 * File Explorer AJAX Handler Class
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
        add_action('wp_ajax_frontend_file_explorer_get_folder_contents', array($this, 'get_folder_contents'));
        add_action('wp_ajax_frontend_file_explorer_create_folder', array($this, 'create_folder'));
        add_action('wp_ajax_frontend_file_explorer_upload_files', array($this, 'upload_files'));
        add_action('wp_ajax_frontend_file_explorer_add_media_files', array($this, 'add_media_files'));
        add_action('wp_ajax_frontend_file_explorer_delete_item', array($this, 'delete_item'));
        add_action('wp_ajax_frontend_file_explorer_download_as_zip', array($this, 'download_as_zip'));
        add_action('wp_ajax_frontend_file_explorer_get_file_link', array($this, 'get_file_link'));

        add_action('wp_ajax_frontend_file_explorer_frontend_get_folder_contents', array($this, 'frontend_get_folder_contents'));
        add_action('wp_ajax_nopriv_frontend_file_explorer_frontend_get_folder_contents', array($this, 'frontend_get_folder_contents'));
        add_action('wp_ajax_frontend_file_explorer_frontend_download_as_zip', array($this, 'frontend_download_as_zip'));
        add_action('wp_ajax_nopriv_frontend_file_explorer_frontend_download_as_zip', array($this, 'frontend_download_as_zip'));
        add_action('wp_ajax_frontend_file_explorer_frontend_get_file_link', array($this, 'frontend_get_file_link'));
        add_action('wp_ajax_nopriv_frontend_file_explorer_frontend_get_file_link', array($this, 'frontend_get_file_link'));
    }

    /**
     * Ensure WP_Filesystem is initialized
     */
    private function ensure_wp_filesystem() {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            WP_Filesystem();
        }
    }

    /**
     * Get allowed MIME types from plugin settings
     *
     * @return array Array of extension => mime type pairs
     */
    private function get_allowed_mime_types() {
        $allowed_types = get_option('frontend_file_explorer_allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar,txt');
        $allowed_extensions = array_map('trim', explode(',', $allowed_types));
        $wp_mimes = wp_get_mime_types();
        $allowed_mimes = array();

        foreach ($allowed_extensions as $ext) {
            $ext = strtolower($ext);
            foreach ($wp_mimes as $ext_pattern => $mime) {
                $pattern_extensions = explode('|', $ext_pattern);
                if (in_array($ext, $pattern_extensions, true)) {
                    $allowed_mimes[$ext] = $mime;
                    break;
                }
            }
        }

        return $allowed_mimes;
    }

    /**
     * Validate a path is within the downloads directory
     *
     * @param string $path Relative path to validate
     * @return string|false Full validated path or false if invalid
     */
    private function validate_path($path) {
        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']) . 'downloads';

        if (strpos($path, '..') !== false) {
            return false;
        }

        $full_path = trailingslashit($base_dir) . ltrim($path, '/');

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_realpath -- no WP_Filesystem equivalent for realpath
        $real_base = realpath($base_dir);
        if ($real_base === false) {
            return $full_path;
        }

        if ($wp_filesystem->exists($full_path)) {
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_realpath -- no WP_Filesystem equivalent for realpath
            $real_path = realpath($full_path);
            if ($real_path === false || strpos($real_path, $real_base) !== 0) {
                return false;
            }
            return $real_path;
        }

        $parent = dirname($full_path);
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_realpath -- no WP_Filesystem equivalent for realpath
        $real_parent = realpath($parent);
        if ($real_parent === false || strpos($real_parent, $real_base) !== 0) {
            return false;
        }

        return $full_path;
    }

    /**
     * Get folder contents (admin)
     */
    public function get_folder_contents() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to access this content.', 'frontend-file-explorer'));
        }

        $this->process_get_folder_contents();
    }

    /**
     * Frontend get folder contents
     *
     * Intentionally allows public browsing of the file explorer.
     * Only listing is allowed; mutating actions require upload_files capability.
     */
    public function frontend_get_folder_contents() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        $this->process_get_folder_contents();
    }

    /**
     * Process get folder contents
     */
    private function process_get_folder_contents() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in calling public methods get_folder_contents() and frontend_get_folder_contents()
        $folder_path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '/';

        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']) . 'downloads';
        $base_url = trailingslashit($upload_dir['baseurl']) . 'downloads';

        $validated = $this->validate_path($folder_path);
        if ($validated === false) {
            wp_send_json_error(__('Invalid folder path.', 'frontend-file-explorer'));
        }

        $full_path = $validated;

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem->exists($full_path) || !$wp_filesystem->is_dir($full_path)) {
            wp_send_json_error(__('The specified folder does not exist.', 'frontend-file-explorer'));
        }

        $items = array();
        $files = $wp_filesystem->dirlist($full_path);

        if (is_array($files)) {
            foreach ($files as $file => $fileinfo) {
                if ($file === '.' || $file === '..' || $file === 'index.php' || $file === '.htaccess') {
                    continue;
                }

                $item_path = trailingslashit($full_path) . $file;
                $is_dir = $fileinfo['type'] === 'd';

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
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        }

        $path = isset($_POST['parent_path']) ? sanitize_text_field(wp_unslash($_POST['parent_path'])) : '/';
        $name = isset($_POST['folder_name']) ? sanitize_file_name(wp_unslash($_POST['folder_name'])) : '';
        if (empty($name) || strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        }

        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']) . 'downloads';
        $full_path = trailingslashit($base_dir) . trim($path, '/') . '/' . $name;

        $validated = $this->validate_path(trim($path, '/') . '/' . $name);
        if ($validated === false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }

        $full_path = $validated;

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        if ($wp_filesystem->exists($full_path)) {
            wp_send_json_error(__('Folder already exists.', 'frontend-file-explorer'));
        }

        if (wp_mkdir_p($full_path)) {
            $wp_filesystem->put_contents($full_path . '/index.php', '<?php // Silence is golden', FS_CHMOD_FILE);
            wp_send_json_success();
        }

        wp_send_json_error(__('Failed to create folder.', 'frontend-file-explorer'));
    }

    /**
     * Upload files
     */
    public function upload_files() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        }

        $path = isset($_POST['folder_path']) ? sanitize_text_field(wp_unslash($_POST['folder_path'])) : '/';
        if (strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }

        if (empty($_FILES['files'])) {
            wp_send_json_error(__('No file uploaded.', 'frontend-file-explorer'));
        }

        $validated = $this->validate_path($path);
        if ($validated === false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }

        $upload_dir = wp_upload_dir();
        $dest_dir = trailingslashit($upload_dir['basedir']) . 'downloads' . trailingslashit($path);
        wp_mkdir_p($dest_dir);

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_FILES cannot be sanitized as a whole; individual file data is validated by wp_handle_upload()
        $uploaded_files = isset($_FILES['files']) ? $_FILES['files'] : null;

        $allowed_mimes = $this->get_allowed_mime_types();
        $upload_overrides = array(
            'test_form' => false,
            'test_type' => true,
            'mimes'     => $allowed_mimes,
        );

        $responses = array('files' => array(), 'errors' => array());

        if ($uploaded_files && is_array($uploaded_files['name'])) {
            $count = count($uploaded_files['name']);
            for ($i = 0; $i < $count; $i++) {
                $file = array(
                    'name'     => $uploaded_files['name'][$i],
                    'type'     => $uploaded_files['type'][$i],
                    'tmp_name' => $uploaded_files['tmp_name'][$i],
                    'error'    => $uploaded_files['error'][$i],
                    'size'     => $uploaded_files['size'][$i]
                );

                $movefile = wp_handle_upload($file, $upload_overrides);
                if ($movefile && !isset($movefile['error'])) {
                    $filename = wp_basename($movefile['file']);
                    $new_file = trailingslashit($dest_dir) . $filename;
                    $wp_filesystem->move($movefile['file'], $new_file);
                    $responses['files'][] = array('name' => sanitize_file_name($file['name']));
                } else {
                    $responses['errors'][] = sprintf(
                        /* translators: %s: file name */
                        esc_html__('Error uploading %s.', 'frontend-file-explorer'),
                        esc_html($file['name'])
                    );
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
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        }

        $path = isset($_POST['folder_path']) ? sanitize_text_field(wp_unslash($_POST['folder_path'])) : '/';
        $attachment_ids = isset($_POST['media_ids']) ? array_map('intval', wp_unslash((array) $_POST['media_ids'])) : array();
        if (empty($attachment_ids) || strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        }

        $upload_dir = wp_upload_dir();
        $dest_dir = trailingslashit($upload_dir['basedir']) . 'downloads' . trailingslashit($path);
        wp_mkdir_p($dest_dir);

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        $allowed_mimes = $this->get_allowed_mime_types();

        foreach ($attachment_ids as $id) {
            $id = intval($id);
            $file_path = get_attached_file($id);
            if ($file_path && $wp_filesystem->exists($file_path)) {
                $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $allowed_extensions = array_keys($allowed_mimes);
                if (!in_array($ext, $allowed_extensions, true)) {
                    continue;
                }
                $wp_filesystem->copy($file_path, trailingslashit($dest_dir) . wp_basename($file_path));
            }
        }
        wp_send_json_success();
    }

    /**
     * Delete item
     */
    public function delete_item() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        }

        $path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';
        $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
        if (empty($path) || strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }

        $validated = $this->validate_path($path);
        if ($validated === false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }

        $full_path = $validated;

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem->exists($full_path)) {
            wp_send_json_error(__('Item does not exist.', 'frontend-file-explorer'));
        }

        $success = false;
        if ($type === 'folder' || $wp_filesystem->is_dir($full_path)) {
            $success = $this->delete_directory($full_path);
        } else {
            $success = wp_delete_file($full_path);
        }

        if ($success) {
            wp_send_json_success(__('Item deleted successfully.', 'frontend-file-explorer'));
        }
        wp_send_json_error(__('Failed to delete item.', 'frontend-file-explorer'));
    }

    /**
     * Download as ZIP (admin)
     */
    public function download_as_zip() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('You do not have permission.', 'frontend-file-explorer'));
        }

        $this->process_download_as_zip();
    }

    /**
     * Download as ZIP (frontend)
     */
    public function frontend_download_as_zip() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');
        $this->process_download_as_zip();
    }

    /**
     * Process download as ZIP
     */
    private function process_download_as_zip() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified in calling public methods download_as_zip() and frontend_download_as_zip()
        $folder_path = isset($_REQUEST['path']) ? sanitize_text_field(wp_unslash($_REQUEST['path'])) : '';

        if (empty($folder_path)) {
            wp_die(esc_html__('No path specified.', 'frontend-file-explorer'));
        }

        if (strpos($folder_path, '..') !== false) {
            wp_die(esc_html__('Invalid path.', 'frontend-file-explorer'));
        }

        $validated = $this->validate_path($folder_path);
        if ($validated === false) {
            wp_die(esc_html__('Invalid path.', 'frontend-file-explorer'));
        }

        $full_path = $validated;

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem->exists($full_path)) {
            wp_die(esc_html__('The requested path does not exist.', 'frontend-file-explorer'));
        }

        $temp_file = get_temp_dir() . 'ffe_' . uniqid('zip_') . '.tmp';

        $zip = new ZipArchive();
        if ($zip->open($temp_file, ZipArchive::CREATE) !== true) {
            wp_die(esc_html__('Could not create ZIP file.', 'frontend-file-explorer'));
        }

        if ($wp_filesystem->is_dir($full_path)) {
            $base_name = basename($full_path);
            $this->add_dir_to_zip($zip, $full_path, $base_name);
        } else {
            $zip->addFile($full_path, basename($full_path));
        }

        $zip->close();

        while (ob_get_level()) {
            ob_end_clean();
        }

        $download_filename = sanitize_file_name(basename($folder_path));
        if (empty($download_filename)) {
            $download_filename = 'folder';
        }
        $download_filename = rawurlencode($download_filename);

        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . $download_filename . '.zip');
        header('Content-Transfer-Encoding: binary');
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_filesize -- no WP_Filesystem alternative for streaming headers
        header('Content-Length: ' . filesize($temp_file));
        header('Cache-Control: private, no-transform, no-store, must-revalidate');

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
        readfile($temp_file);

        wp_delete_file($temp_file);

        exit;
    }

    /**
     * Get file link (admin)
     */
    public function get_file_link() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');

        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('Permission denied.', 'frontend-file-explorer'));
        }

        $this->process_get_file_link();
    }

    /**
     * Get file link (frontend)
     */
    public function frontend_get_file_link() {
        check_ajax_referer('frontend_file_explorer_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in to perform this action.', 'frontend-file-explorer'));
        }

        $this->process_get_file_link();
    }

    /**
     * Process get file link
     */
    private function process_get_file_link() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in calling public methods get_file_link() and frontend_get_file_link()
        $path = isset($_POST['path']) ? sanitize_text_field(wp_unslash($_POST['path'])) : '';

        if (empty($path)) {
            wp_send_json_error(__('Invalid request.', 'frontend-file-explorer'));
        }

        if (strpos($path, '..') !== false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }

        $validated = $this->validate_path($path);
        if ($validated === false) {
            wp_send_json_error(__('Invalid path.', 'frontend-file-explorer'));
        }

        $full_path = $validated;

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem->exists($full_path) || $wp_filesystem->is_dir($full_path)) {
            wp_send_json_error(__('The specified file does not exist.', 'frontend-file-explorer'));
        }

        $uploads_dir = wp_upload_dir();
        $url = esc_url($uploads_dir['baseurl'] . '/downloads' . $path);

        wp_send_json_success($url);
    }

    /**
     * Delete directory recursively
     */
    private function delete_directory($dir) {
        $this->ensure_wp_filesystem();
        global $wp_filesystem;

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

        $this->ensure_wp_filesystem();
        global $wp_filesystem;

        $files = $wp_filesystem->dirlist($dir);
        if (is_array($files)) {
            foreach ($files as $file => $fileinfo) {
                if ($file === '.' || $file === '..' || $file === 'index.php' || $file === '.htaccess') {
                    continue;
                }

                $path = $dir . '/' . $file;

                if ($fileinfo['type'] === 'd') {
                    $this->add_dir_to_zip($zip, $path, $new_dir . '/' . $file);
                } else {
                    $zip->addFile($path, $new_dir . '/' . $file);
                }
            }
        }
    }
}
