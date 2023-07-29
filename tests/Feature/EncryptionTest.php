<?php
namespace rhmdarif\Library\Tests;

use Orchestra\Testbench\TestCase;
use rhmdarif\Library\Encryption;

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