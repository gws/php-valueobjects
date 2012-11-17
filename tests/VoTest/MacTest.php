<?php

namespace VoTest;

use InvalidArgumentException;
use Vo\Mac;

class MacTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider rawMacProvider
     */
    public function testCreation($raw, $is_valid)
    {
        try {
            $mac = new Mac($raw);
        } catch (InvalidArgumentException $e) {
            if (!$is_valid) {
                return;
            }

            throw $e;
        }
    }

    public function testFormatting()
    {
        $raw = '00-fa-22-33-44-55';
        $mac = new Mac($raw);

        $this->assertEquals(
            '00fa22334455',
            $mac->format(false, '', 0)
        );

        $this->assertEquals(
            '00:fa:22:33:44:55',
            $mac->format(false, ':', 2)
        );

        $this->assertEquals(
            '00fa:2233:4455',
            $mac->format(false, ':', 4)
        );

        $this->assertEquals(
            '00FA.2233.4455',
            $mac->format(true, '.', 4)
        );

        $this->assertEquals(
            '00FA-2233-4455',
            $mac->format(true, '-', 4)
        );

        $this->assertEquals(
            '00f-a22-334-455',
            $mac->format(false, '-', 3)
        );
    }

    public function testToString()
    {
        $raw = '00-fa-22-33-44-55';
        $mac = new Mac($raw);

        $this->assertEquals(
            '00fa22334455',
            (string)$mac
        );
    }

    public function rawMacProvider()
    {
        return array(
            array('001122334455', true), // bare
            array('00:11:22:33:aa:55', true), // typical
            array('00-fa-22-33-44-55', true), // Windows
            array('00-ha-22-33-44-55', false), // invalid hex digit
            array('00bb.2233.4455', true) // Cisco
        );
    }
}
