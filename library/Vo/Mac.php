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
 * Encapsulate a MAC address
 *
 * @package ValueObjects
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
