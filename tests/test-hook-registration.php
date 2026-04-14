<?php
/**
 * Tests shortcode and admin hooks are registered
 */

class TestHookRegistration extends \PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        $GLOBALS['mock_actions'] = array();
        $GLOBALS['mock_shortcodes'] = array();
        new Frontend_File_Explorer();
    }

    public function test_shortcode_registered() {
        $this->assertArrayHasKey(
            'frontend_file_explorer',
            $GLOBALS['mock_shortcodes'],
            'Shortcode frontend_file_explorer must be registered'
        );
    }

    public function test_shortcode_callback_is_valid() {
        $callback = $GLOBALS['mock_shortcodes']['frontend_file_explorer'];
        $this->assertIsArray($callback);
        $this->assertTrue(method_exists($callback[0], $callback[1]));
    }

    public function test_admin_menu_hook_registered() {
        $hooks = array_column($GLOBALS['mock_actions'], 'hook');
        $this->assertContains('admin_menu', $hooks, 'admin_menu hook must be registered');
    }

    public function test_admin_enqueue_scripts_hook_registered() {
        $hooks = array_column($GLOBALS['mock_actions'], 'hook');
        $this->assertContains('admin_enqueue_scripts', $hooks, 'admin_enqueue_scripts hook must be registered');
    }

    public function test_main_class_methods_exist() {
        $instance = Frontend_File_Explorer::instance();

        $required_methods = array(
            'instance',
            'activate',
            'deactivate',
            'add_admin_menu',
            'render_admin_page',
            'admin_enqueue_scripts',
            'frontend_file_explorer_shortcode',
        );

        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists($instance, $method),
                "Method '{$method}' must exist on Frontend_File_Explorer"
            );
        }
    }

    public function test_ajax_class_methods_exist() {
        $ajax = new Frontend_File_Explorer_Ajax();

        $required_methods = array(
            'get_folder_contents',
            'frontend_get_folder_contents',
            'create_folder',
            'upload_files',
            'add_media_files',
            'delete_item',
            'download_as_zip',
            'frontend_download_as_zip',
            'get_file_link',
            'frontend_get_file_link',
        );

        foreach ($required_methods as $method) {
            $this->assertTrue(
                method_exists($ajax, $method),
                "Method '{$method}' must exist on Frontend_File_Explorer_Ajax"
            );
        }
    }
}
