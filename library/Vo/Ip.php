<?php
/**
 * PHP Value Objects
 *
 * @author    Gordon Stratton <gordon.stratton@gmail.com>
 * @copyright 2011-2012 Gordon Stratton
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD 2-Clause
 * @link      https://github.com/strattg/php-valueobjects
 * @package   ValueObjects
 */

namespace Vo;

use InvalidArgumentException;

/**
 * Encapsulate an IP address
 *
 * This class supports both IPv4 and IPv6 addresses.
 *
 * @package ValueObjects
 */
class Ip
{
    /**
     * IP address in packed in_addr representation
     *
     * @var string
     */
    protected $ip;

    /**
     * Version of the IP address
     *
     * @var int
     */
    protected $version;

    /**
     * @param  string $packed
     * @return int
     * @throws InvalidArgumentException when $packed is not a supported IP
     *                                  version
     */
    public static function getVersion($packed)
    {
        $byteCount = mb_strlen($packed, '8bit');

        if (!in_array($byteCount, array(16, 4))) {
            throw new InvalidArgumentException(
                'Only IPv4 or IPv6 addresses are supported.'
            );
        }

        return $byteCount === 16 ? 6 : 4;
    }

    /**
     * @return string
     * @throws InvalidArgumentException when $input is not recognized by
     *                                  inet_pton()
     */
    public static function normalize($input)
    {
        $packed = @inet_pton($input);

        if ($packed === false) {
            throw new InvalidArgumentException('Invalid IP address.');
        }

        return $packed;
    }

    /**
     * Accepts a string representation of an IP address and create an IP object
     *
     * @param  string $ip
     * @throws InvalidArgumentException when $ip is invalid
     */
    public function __construct($ip)
    {
        $this->ip = self::normalize($ip);
        $this->version = self::getVersion($this->ip);
    }

    /**
     * Creates an IP object from an array of integers
     *
     * One likely use for this is to store an IP address in a database in an
     * address-agnostic fashion.
     *
     * Note: The array of integers must be most-significant-integer first
     *
     * @param  array $integers Integer array, most-significant first
     * @return Ip
     */
    public static function fromIntegerArray(array $integers)
    {
        if (count($integers) !== 4) {
            throw new InvalidArgumentException(
                'Wrong number of integers; expected 4'
            );
        }

        list($i4, $i3, $i2, $i1) = $integers;

        if ($i4 == 0 && $i3 == 0 && $i2 == 0x0000ffff) {
            $packed = pack('N1', $i1);
        } else {
            $packed = pack('N4', $i4, $i3, $i2, $i1);
        }

        return new static(inet_ntop($packed));
    }

    /**
     * Formats the IP address using {@link inet_ntop}
     *
     * @return string
     */
    public function format()
    {
        return inet_ntop($this->ip);
    }

    /**
     * Marshals an IP object to an array of integers
     *
     * One likely use for this is to store an IP address in a database in an
     * address-agnostic fashion.
     *
     * Note: The array of integers will be most-significant-integer first
     *
     * @return array
     */
    public function toIntegerArray()
    {
        $i4 = $i3 = $i2 = $i1 = 0;

        if ($this->getVersion() === 4) {
            $i2 = 0x0000ffff;
            list(, $i1) = unpack('N1', $this->ip);
        } else {
            list(, $i4, $i3, $i2, $i1) = unpack('N4', $this->ip);
        }

        // The checks and additions below work around the effects described in
        // the "Caution" message on 32-bit systems here:
        //
        // http://php.net/manual/en/function.unpack.php
        return array(
            $i4 < 0 ? $i4 + 4294967296 : $i4,
            $i3 < 0 ? $i3 + 4294967296 : $i3,
            $i2 < 0 ? $i2 + 4294967296 : $i2,
            $i1 < 0 ? $i1 + 4294967296 : $i1
        );
    }

    /**
     * Tests if an IP address belongs to a specific network
     *
     * Accepts a network base address and a prefix length.
     *
     * @param  Ip   $base      Base address for the network to test
     * @param  int  $prefixlen Prefix length to test
     * @return bool
     */
    public function isInNetwork(Ip $base, $prefixlen)
    {
        $baseVersion = $base->getVersion();
        $thisVersion = $this->getVersion();

        if ($prefixlen < 0) {
            throw new InvalidArgumentException(
                'Prefix length cannot be negative'
            );
        }

        if ($baseVersion !== $thisVersion) {
            throw new InvalidArgumentException(
                'Address version does not match supplied network base version'
            );
        }

        if ($thisVersion === 4 && $prefixlen > 32) {
            throw new InvalidArgumentException(
                'Address version (4) was not supplied a correct prefix length'
            );
        }

        if ($thisVersion === 6 && $prefixlen > 128) {
            throw new InvalidArgumentException(
                'Address version (6) was not supplied a correct prefix length'
            );
        }

        // First create a 128-bit long binary string regardless of IP version.
        // IPv4 addresses are left-padded with zeroes so that the algorithm
        // can be used generically.
        //
        // Next, split the 128-bit string into 32-bit chunks, and convert them
        // to unsigned integers. We can then use this to perform the bitwise
        // comparisons that we need later.
        $prefixIntegerArray = array_map(
            'bindec',
            str_split(
                str_pad(
                    str_pad(
                        str_repeat('1', $prefixlen),
                        $thisVersion === 4 ? 32 : 128,
                        '0'
                    ),
                    128,
                    '0',
                    STR_PAD_LEFT
                ),
                32
            )
        );

        $baseIntegerArray = $base->toIntegerArray();
        $thisIntegerArray = $this->toIntegerArray();

        // If it's version 4, we just want to compare final array position
        $start = $thisVersion === 4 ? 3 : 0;

        for ($i = $start; $i < 4; $i++) {
            $a = $thisIntegerArray[$i];
            $b = $baseIntegerArray[$i];
            $p = $prefixIntegerArray[$i];

            // If the address ANDed with the prefix mask is not equal to the
            // base address ANDed with the prefix mask, we can bail.
            if (($a & $p) !== ($b & $p)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the IP version of this object
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Formats the IP object according to @see {format()}
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }
}
