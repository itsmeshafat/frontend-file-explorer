<?php
/**
 * Tests that all AJAX hooks are registered correctly
 */

class TestAjaxHooks extends \PHPUnit\Framework\TestCase {

    private $expected_admin_actions = array(
        'wp_ajax_frontend_file_explorer_get_folder_contents',
        'wp_ajax_frontend_file_explorer_create_folder',
        'wp_ajax_frontend_file_explorer_upload_files',
        'wp_ajax_frontend_file_explorer_add_media_files',
        'wp_ajax_frontend_file_explorer_delete_item',
        'wp_ajax_frontend_file_explorer_download_as_zip',
        'wp_ajax_frontend_file_explorer_get_file_link',
    );

    private $expected_frontend_actions = array(
        'wp_ajax_frontend_file_explorer_frontend_get_folder_contents',
        'wp_ajax_nopriv_frontend_file_explorer_frontend_get_folder_contents',
        'wp_ajax_frontend_file_explorer_frontend_download_as_zip',
        'wp_ajax_nopriv_frontend_file_explorer_frontend_download_as_zip',
        'wp_ajax_frontend_file_explorer_frontend_get_file_link',
        'wp_ajax_nopriv_frontend_file_explorer_frontend_get_file_link',
    );

    protected function setUp(): void {
        $GLOBALS['mock_actions'] = array();
        new Frontend_File_Explorer_Ajax();
    }

    public function test_all_admin_ajax_actions_registered() {
        $registered_hooks = array_column($GLOBALS['mock_actions'], 'hook');

        foreach ($this->expected_admin_actions as $action) {
            $this->assertContains(
                $action,
                $registered_hooks,
                "Admin AJAX action '{$action}' must be registered"
            );
        }
    }

    public function test_all_frontend_ajax_actions_registered() {
        $registered_hooks = array_column($GLOBALS['mock_actions'], 'hook');

        foreach ($this->expected_frontend_actions as $action) {
            $this->assertContains(
                $action,
                $registered_hooks,
                "Frontend AJAX action '{$action}' must be registered"
            );
        }
    }

    public function test_total_ajax_actions_count() {
        $total_expected = count($this->expected_admin_actions) + count($this->expected_frontend_actions);
        $this->assertCount(
            $total_expected,
            $GLOBALS['mock_actions'],
            "Expected exactly {$total_expected} AJAX actions to be registered"
        );
    }

    public function test_all_callbacks_are_valid() {
        foreach ($GLOBALS['mock_actions'] as $registration) {
            $callback = $registration['callback'];
            $this->assertIsArray($callback, 'Callback should be an array [object, method]');
            $this->assertCount(2, $callback, 'Callback should have exactly 2 elements');
            $this->assertIsObject($callback[0], 'First element should be an object instance');
            $this->assertIsString($callback[1], 'Second element should be a method name');
            $this->assertTrue(
                method_exists($callback[0], $callback[1]),
                "Method '{$callback[1]}' must exist on " . get_class($callback[0])
            );
        }
    }
}
