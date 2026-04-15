<?php
/**
 * PHPUnit bootstrap file — mocks WordPress functions for testing
 */

define('ABSPATH', 'C:\laragon\www\pcp/');

define('WP_CONTENT_DIR', ABSPATH . 'wp-content/');
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . 'plugins/');

define('FRONTEND_FILE_EXPLORER_VERSION', '1.0.4');
define('FRONTEND_FILE_EXPLORER_PLUGIN_DIR', dirname(__DIR__) . '/');
define('FRONTEND_FILE_EXPLORER_PLUGIN_URL', 'http://localhost/plugins/frontend-file-explorer/');
define('FRONTEND_FILE_EXPLORER_PLUGIN_BASENAME', 'frontend-file-explorer/frontend-file-explorer.php');
define('FRONTEND_FILE_EXPLORER_UPLOADS_DIR', sys_get_temp_dir() . '/ffe-test-uploads/downloads');
define('FRONTEND_FILE_EXPLORER_UPLOADS_URL', 'http://localhost/wp-content/uploads/downloads');
define('FS_CHMOD_FILE', 0644);

require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once dirname(__DIR__) . '/includes/class-frontend-file-explorer.php';
require_once dirname(__DIR__) . '/includes/class-frontend-file-explorer-ajax.php';

$GLOBALS['mock_actions'] = array();
$GLOBALS['mock_shortcodes'] = array();
$GLOBALS['mock_filters'] = array();
$GLOBALS['mock_options'] = array(
    'frontend_file_explorer_allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar,txt',
);

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        $GLOBALS['mock_actions'][] = array(
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
        );
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        $GLOBALS['mock_filters'][] = array(
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
        );
    }
}

if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $callback) {
        $GLOBALS['mock_shortcodes'][$tag] = $callback;
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return FRONTEND_FILE_EXPLORER_PLUGIN_URL;
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return FRONTEND_FILE_EXPLORER_PLUGIN_BASENAME;
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return array(
            'basedir' => sys_get_temp_dir() . '/ffe-test-uploads',
            'baseurl' => 'http://localhost/wp-content/uploads',
            'path' => sys_get_temp_dir() . '/ffe-test-uploads',
            'url' => 'http://localhost/wp-content/uploads',
            'error' => false,
        );
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return strip_tags($str);
    }
}

if (!function_exists('sanitize_file_name')) {
    function sanitize_file_name($name) {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $name);
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) {
        return is_string($value) ? stripslashes($value) : $value;
    }
}

if (!function_exists('trailingslashit')) {
    function trailingslashit($string) {
        return rtrim($string, '/') . '/';
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '', $scheme = 'admin') {
        return 'http://localhost/wp-admin/' . ltrim($path, '/');
    }
}

if (!function_exists('get_option')) {
    function get_option($key, $default = false) {
        return isset($GLOBALS['mock_options'][$key]) ? $GLOBALS['mock_options'][$key] : $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($key, $value) {
        $GLOBALS['mock_options'][$key] = $value;
        return true;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'test_nonce_' . md5($action);
    }
}

if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action = -1, $query_arg = 'nonce', $die = true) {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true;
    }
}

if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in() {
        return true;
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        throw new \RuntimeException('JSON_SUCCESS:' . json_encode($data));
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null) {
        throw new \RuntimeException('JSON_ERROR:' . json_encode($data));
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        throw new \RuntimeException('WP_DIE:' . $message);
    }
}

if (!function_exists('flush_rewrite_rules')) {
    function flush_rewrite_rules() {}
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {}
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {}
}

if (!function_exists('add_menu_page')) {
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback, $icon, $position) {
        return 'toplevel_page_' . $menu_slug;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all') {}
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = false, $deps = array(), $ver = false, $in_footer = false) {}
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {}
}

if (!function_exists('wp_enqueue_media')) {
    function wp_enqueue_media() {}
}

if (!function_exists('wp_max_upload_size')) {
    function wp_max_upload_size() {
        return 8388608;
    }
}

if (!function_exists('wp_get_mime_types')) {
    function wp_get_mime_types() {
        return array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'rar' => 'application/rar',
            'txt' => 'text/plain',
        );
    }
}

if (!function_exists('wp_handle_upload')) {
    function wp_handle_upload($file, $overrides = array()) {
        return array('file' => sys_get_temp_dir() . '/uploaded_' . $file['name'], 'url' => 'http://localhost/' . $file['name']);
    }
}

if (!function_exists('wp_basename')) {
    function wp_basename($path, $suffix = '') {
        return basename($path, $suffix);
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return is_dir($dir);
    }
}

if (!function_exists('wp_delete_file')) {
    function wp_delete_file($file) {
        return file_exists($file) ? unlink($file) : true;
    }
}

if (!function_exists('get_temp_dir')) {
    function get_temp_dir() {
        return sys_get_temp_dir() . '/';
    }
}

if (!function_exists('get_attached_file')) {
    function get_attached_file($id) {
        return false;
    }
}

if (!class_exists('WP_Filesystem_Direct')) {
    class WP_Filesystem_Direct {
        public function exists($path) {
            return file_exists($path);
        }
        public function is_dir($path) {
            return is_dir($path);
        }
        public function dirlist($path, $include_hidden = true, $recursive = false) {
            if (!is_dir($path)) return false;
            $files = scandir($path);
            $result = array();
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $full = $path . '/' . $f;
                $result[$f] = array(
                    'name' => $f,
                    'type' => is_dir($full) ? 'd' : 'f',
                    'size' => is_file($full) ? filesize($full) : 0,
                );
            }
            return $result;
        }
        public function put_contents($file, $contents, $mode = 0644) {
            $dir = dirname($file);
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            return file_put_contents($file, $contents) !== false;
        }
        public function get_contents($file) {
            return file_get_contents($file);
        }
        public function delete($file, $recursive = false, $type = false) {
            if (is_dir($file)) {
                return rmdir($file);
            }
            return file_exists($file) ? unlink($file) : true;
        }
        public function rmdir($path) {
            return is_dir($path) ? rmdir($path) : true;
        }
        public function copy($source, $dest) {
            return copy($source, $dest);
        }
        public function move($source, $dest) {
            return rename($source, $dest);
        }
        public function size($file) {
            return filesize($file);
        }
    }
}

if (!function_exists('WP_Filesystem')) {
    function WP_Filesystem() {
        global $wp_filesystem;
        $wp_filesystem = new WP_Filesystem_Direct();
        return true;
    }
}
