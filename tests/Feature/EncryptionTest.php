<?php
namespace rahmadArif\PanelComposerPackages\Tests;

use Orchestra\Testbench\TestCase;
use rahmadArif\PanelComposerPackages\Helpers\Encryption;

class EncryptionTest extends TestCase
{
    /**
    * @test
    */
    public function myFirstTest() {
        $encryption = new Encryption("aaaa");
        $ori = "arif";
        $this->assertTrue(($ori == $encryption->decode($encryption->encode($ori))));
    }
}