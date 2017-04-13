<?php

use Ampersa\SURBL\SURBL;

class SURBLTest extends PHPUnit_Framework_TestCase
{
    public function testListedPassesOnTrue()
    {
        $surbl = new SURBL;
        $result1 = $surbl->listed('127.0.0.8');
        $result2 = $surbl->listed('127.0.0.16');
        $result3 = $surbl->listed('127.0.0.32');
        $result4 = $surbl->listed('127.0.0.64');
        $result5 = $surbl->listed('127.0.0.128');

        $this->assertTrue($result1);
        $this->assertTrue($result2);
        $this->assertFalse($result3);
        $this->assertTrue($result4);
        $this->assertTrue($result5);
    }

    public function testListedPassesOnMultiple()
    {
        $surbl = new SURBL;
        $result = $surbl->listed('127.0.0.24');

        $this->assertTrue($result);
    }

    public function testListedValidatesWithOptions()
    {
        $surbl1 = new SURBL(SURBL::LIST_PH);
        $surbl2 = new SURBL(SURBL::LIST_PH | SURBL::LIST_MW);

        $result1 = $surbl1->listed('127.0.0.16', SURBL::LIST_PH);
        $result2 = $surbl2->listed('127.0.0.16', SURBL::LIST_PH | SURBL::LIST_MW);

        $this->assertFalse($result1);
        $this->assertTrue($result2);
    }

    public function testCanCallStatic()
    {
        $result = SURBL::isListed('127.0.0.8', SURBL::LIST_PH | SURBL::LIST_MW | SURBL::LIST_ABUSE | SURBL::LIST_CR);

        $this->assertTrue($result);
    }

    public function testUnlistedDomain()
    {
        $surbl = new SURBL;
        $result = $surbl->listed('http://surbl.org/');

        $this->assertFalse($result);
    }

    public function testNxDomain()
    {
        $surbl = new SURBL;
        $result = $surbl->listed('http://surbl-does-not-exist.org/');

        $this->assertFalse($result);
    }

    public function testListedDomain()
    {
        $surbl = new SURBL;
        $result = $surbl->listed('http://surbl-org-permanent-test-point.com/');

        $this->assertTrue($result);
    }

    public function testGracefulFailOnBadDomain()
    {
        $surbl = new SURBL;
        $result = $surbl->listed('not a valid URL');

        $this->assertFalse($result);
    }

    public function testEmptyUrlReturnFalse()
    {
        $surbl = new SURBL;
        $result = $surbl->listed('');

        $this->assertFalse($result);
    }
}
