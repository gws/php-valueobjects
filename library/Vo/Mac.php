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
 * Encapsulate a MAC address
 *
 * @category Vo
 * @package Vo
 */
class Mac
{
    /**
     * Normalized MAC address (internal format)
     *
     * @var string
     */
    protected $mac;

    /**
     * Constructor
     *
     * Accepts an EUI-48 (MAC) address in any valid format.
     *
     * @param string $raw Raw MAC address
     */
    public function __construct($raw)
    {
        // Remove all non-hex characters
        $mac = preg_replace('/[^[:xdigit:]]/', '', $raw);

        // Check if the remaining characters are the right length
        if (strlen($mac) !== 12) {
            throw new InvalidArgumentException('Invalid MAC address.');
        }

        // Lowercase the whole thing
        $mac = strtolower($mac);

        $this->mac = $mac;
    }

    /**
     * Formats the MAC address in a configurable way
     *
     * @param string $upper Whether or not to uppercase the formatted address
     * @param string $delimiter The delimiter to use between groups 
     * @param string $groupLength The length of delimited hex digit groups (0 for none)
     * @return string
     */
    public function format($upper = false, $delimiter = ':', $groupLength = 2)
    {
        $formatted = $this->mac;

        if ($upper) {
            $formatted = strtoupper($formatted);
        }

        if ($groupLength > 0) {
            $formatted = implode($delimiter, str_split($formatted, $groupLength));
        }

        return $formatted;
    }

    /**
     * Displays this MAC address
     *
     * The MAC address is displayed in a lowercased, non-delimited format.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->mac;
    }
}
