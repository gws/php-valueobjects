<?php

namespace VoTest;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Vo\Ip;

class IpTest extends TestCase
{
    /**
     * @dataProvider rawIpProvider
     */
    public function testCreation($raw, $is_valid)
    {
        if (!$is_valid) {
            $this->expectException(InvalidArgumentException::class);
        }

        $ip = new Ip($raw);
        $this->assertInstanceOf(Ip::class, $ip);
    }

    public function testFormat()
    {
        $raw = '200.199.198.197';
        $ip = new Ip($raw);

        $this->assertEquals(
            $raw,
            $ip->format()
        );

        $raw = '2001:420:dead:beef:babe:0420:4:5';
        $ip = new Ip($raw);

        $this->assertEquals(
            '2001:420:dead:beef:babe:420:4:5',
            $ip->format()
        );
    }

    /**
     * @dataProvider integerArrayProvider
     */
    public function testFromIntegerArray($array, $presentation)
    {
        $ip = Ip::fromIntegerArray($array);

        $this->assertEquals(
            $presentation,
            $ip->format()
        );
    }

    /**
     * @dataProvider integerArrayProvider
     */
    public function testToIntegerArray($array, $presentation)
    {
        $ip = new Ip($presentation);

        $this->assertEquals(
            $array,
            $ip->toIntegerArray()
        );
    }

    public function testGetVersion()
    {
        $ip = new Ip('200.193.104.32');

        $this->assertEquals(
            4,
            $ip->getVersion()
        );

        $ip = new Ip('2001:304:234a::1');

        $this->assertEquals(
            6,
            $ip->getVersion()
        );
    }

    public function testIsInNetwork()
    {
        $ip = new Ip('2001:304:234a::1');

        $this->assertEquals(
            true,
            $ip->isInNetwork(new Ip('2001:304:234a::'), 48)
        );

        $ip = new Ip('2001:304:234b::1');

        $this->assertEquals(
            false,
            $ip->isInNetwork(new Ip('2001:304:234a::'), 48)
        );

        $ip = new Ip('127.2.4.5');

        $this->assertEquals(
            true,
            $ip->isInNetwork(new Ip('127.2.3.4'), 16)
        );

        $ip = new Ip('127.2.127.9');

        $this->assertEquals(
            false,
            $ip->isInNetwork(new Ip('127.2.128.0'), 17)
        );
    }

    public function rawIpProvider()
    {
        return array(
            array('200.193.104.32', true),
            array('2001:304:234a::1', true),
            array('200.193.104.32', true),
            array('2001:304:234a::1', true),
            array('256.245.243.241', false),
            array('fart:lol:234::', false),
            array('::1', true)
        );
    }

    public function integerArrayProvider()
    {
        return array(
            array(
                array(
                    0x0,
                    0x0,
                    0x0000ffff,
                    0xa010105
                ),
                '10.1.1.5'
            ),
            array(
                array(
                    0x0,
                    0x0,
                    0x0000ffff,
                    0xfefdfcfb
                ),
                '254.253.252.251'
            ),
            array(
                array(
                    0x0,
                    0x0,
                    0x0,
                    0x1
                ),
                '::1'
            ),
            array(
                array(
                    0xdeadbeef,
                    0xbadbabe,
                    0x0,
                    0xa
                ),
                'dead:beef:bad:babe::a'
            )
        );
    }
}
