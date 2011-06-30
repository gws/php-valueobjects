<?php

/**
 * PHP Value Objects
 *
 * @category Vo
 * @package Vo
 */

/**
 * Copyright 2011 Gordon Stratton. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of
 *    conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list
 *    of conditions and the following disclaimer in the documentation and/or other materials
 *    provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY GORDON STRATTON ``AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL GORDON STRATTON OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those of the
 * authors and should not be interpreted as representing official policies, either expressed
 * or implied, of Gordon Stratton.
 */

namespace Vo;

use InvalidArgumentException;

/**
 * Encapsulate an IP address
 *
 * This class supports both IPv4 and IPv6 addresses.
 *
 * @category Vo
 * @package Vo
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
     * Length of the packed in_addr IP address, in bytes
     *
     * @var int
     */
    protected $numBytes;

    /**
     * Version of the IP address
     *
     * @var int
     */
    protected $version;

    /**
     * Constructor
     *
     * Accepts an IPv4 or an IPv6 address in any valid format.
     *
     * @param string $raw Raw IP address
     */
    public function __construct($raw)
    {
        $packed = @inet_pton($raw);

        if ($packed === false) {
            throw new InvalidArgumentException(
                'Invalid IP address: inet_pton failed to understand it.'
            );
        }

        $this->ip = $packed;
        $this->numBytes = mb_strlen($packed, '8bit');

        if ($this->numBytes !== 4 && $this->numBytes !== 16) {
            throw new InvalidArgumentException(
                'Invalid IP address: length in bytes must be 4 or 16.'
            );
        }

        $this->version = $this->numBytes === 4 ? 4 : 6;
    }

    /**
     * Constructs an Ip object from an array of integers
     *
     * One likely use for this is to store an IP address in a database in an
     * address-agnostic fashion.
     *
     * Note: The array of integers must be most-significant-integer first
     *
     * @param $integers Integer array, most-significant first
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

        return new self(inet_ntop($packed));
    }

    /**
     * Formats the IP address using inet_ntop
     *
     * @return string
     */
    public function format()
    {
        return inet_ntop($this->ip);
    }

    /**
     * Marshals an Ip object to an array of integers
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

        if ($this->numBytes === 4) {
            $i2 = 0x0000ffff;
            list(, $i1) = unpack('N1', $this->ip);
        } elseif ($this->numBytes === 16) {
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
     * @param $base Base address for the network to test
     * @param int $prefixlen Prefix length to test
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
     * @see format()
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }
}
