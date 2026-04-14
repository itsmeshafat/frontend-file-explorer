<?php
/**
 * Tests class instantiation after rename
 */

class TestClassInstantiation extends \PHPUnit\Framework\TestCase {

    public function test_main_class_exists() {
        $this->assertTrue(class_exists('Frontend_File_Explorer'), 'Frontend_File_Explorer class should exist');
    }

    public function test_ajax_class_exists() {
        $this->assertTrue(class_exists('Frontend_File_Explorer_Ajax'), 'Frontend_File_Explorer_Ajax class should exist');
    }

    public function test_main_class_can_instantiate() {
        $instance = new Frontend_File_Explorer();
        $this->assertInstanceOf(Frontend_File_Explorer::class, $instance);
    }

    public function test_ajax_class_can_instantiate() {
        $instance = new Frontend_File_Explorer_Ajax();
        $this->assertInstanceOf(Frontend_File_Explorer_Ajax::class, $instance);
    }

    public function test_old_class_names_do_not_exist() {
        $this->assertFalse(class_exists('FrontendFileExplorer'), 'Old class name FrontendFileExplorer must not exist');
        $this->assertFalse(class_exists('FrontendFileExplorerAjax'), 'Old class name FrontendFileExplorerAjax must not exist');
    }
}
