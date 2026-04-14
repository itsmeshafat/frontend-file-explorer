<?php
/**
 * Tests singleton pattern works correctly after rename
 */

class TestSingletonPattern extends \PHPUnit\Framework\TestCase {

    public function test_instance_returns_same_object() {
        $a = Frontend_File_Explorer::instance();
        $b = Frontend_File_Explorer::instance();
        $this->assertSame($a, $b, 'Singleton should return the exact same instance');
    }

    public function test_instance_returns_correct_class() {
        $instance = Frontend_File_Explorer::instance();
        $this->assertInstanceOf(Frontend_File_Explorer::class, $instance);
    }

    public function test_ajax_instance_is_independent() {
        $a = new Frontend_File_Explorer_Ajax();
        $b = new Frontend_File_Explorer_Ajax();
        $this->assertNotSame($a, $b, 'AJAX class is not a singleton, instances should be different');
    }
}
