<?php
/**
 * PHP Value Objects
 *
 * @author    Gordon Stratton <gordon.stratton@gmail.com>
 * @copyright 2011-2014 Gordon Stratton
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD 2-Clause
 * @link      https://github.com/gws/php-valueobjects
 */

namespace Vo;

use InvalidArgumentException;

/**
 * Encapsulate a MAC address
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
     * Normalizes formatted MAC address into a lowercased hex string
     *
     * @param  string $input
     * @return string
     * @throws InvalidArgumentException on invalid MAC addresses
     */
    public static function normalize($input)
    {
        $nonHexRemoved = preg_replace('/[^[:xdigit:]]/', '', $input);

        if (strlen($nonHexRemoved) !== 12) {
            throw new InvalidArgumentException('Invalid MAC address.');
        }

        return strtolower($nonHexRemoved);
    }

    /**
     * Constructor
     *
     * Accepts an EUI-48 (MAC) address in any valid format.
     *
     * @param string $eui48
     */
    public function __construct($eui48)
    {
        $this->mac = self::normalize($eui48);
    }

    /**
     * Formats the MAC address in a configurable way
     *
     * @param  string $upper       Whether or not to uppercase the formatted address
     * @param  string $delimiter   The delimiter to use between groups
     * @param  string $groupLength The length of delimited hex digit groups (0 for none)
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
