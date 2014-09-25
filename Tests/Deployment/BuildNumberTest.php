<?php
namespace WindowsAzure\DistributionBundle\Tests\Deployment;

use WindowsAzure\DistributionBundle\Deployment\BuildNumber;

class BuildNumberTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (file_exists(sys_get_temp_dir() . "/azure_build_number.yml")) {
            unlink(sys_get_temp_dir() . "/azure_build_number.yml");
        }
    }

    public function testNewBuilNumberIsZero()
    {
        $number = BuildNumber::createInDirectory(sys_get_temp_dir());
        
        $this->assertEquals(0, $number->get());
    }

    public function testIncrement()
    {
        $number = BuildNumber::createInDirectory(sys_get_temp_dir());
        $old = $number->get();
        
        $new = $number->increment();
        
        $this->assertEquals($old + 1, $new);
        $this->assertEquals($new, $number->get());
    }
}
