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

use Locale;
use NumberFormatter;
use InvalidArgumentException;

/**
 * Money value object
 *
 * @see     http://martinfowler.com/eaaCatalog/money.html
 * @package ValueObjects
 */
class Money
{
    /**
     * Internal amount
     *
     * @var string
     */
    protected $amount;

    /**
     * Internal currency as a 3-digit ISO 4217 code
     *
     * @var string
     */
    protected $currency;

    /**
     * Currency formatter (requires the intl extension)
     *
     * @var NumberFormatter
     */
    protected $formatter;

    /**
     * Scale to use for calculations
     *
     * @var int
     * @link http://www.php.net/manual/en/function.bcscale.php
     */
    protected $scale;

    /**
     * Default currency formatter (requires the intl extension)
     *
     * @var NumberFormatter
     */
    protected static $defaultFormatter = null;

    /**
     * Default scale to use for calculations
     *
     * @var int
     * @see http://www.php.net/manual/en/function.bcscale.php
     */
    protected static $defaultScale = 20;

    /**
     * Set the default formatter
     *
     * @param NumberFormatter $formatter
     */
    public static function setDefaultFormatter(NumberFormatter $formatter)
    {
        self::$defaultFormatter = $formatter;
    }

    /**
     * Set the default scale to use for calculations
     *
     * @param int $scale
     */
    public static function setDefaultScale($scale)
    {
        self::$defaultScale = (int) $scale;
    }

    /**
     * Get the default formatter
     *
     * This will create a formatter based on the Locale default if one is not
     * set prior to this method being called.
     *
     * @return NumberFormatter
     */
    public static function getDefaultFormatter()
    {
        if (null === self::$defaultFormatter) {
            self::setDefaultFormatter(
                new NumberFormatter(
                    Locale::getDefault(),
                    NumberFormatter::CURRENCY
                )
            );
        }

        return self::$defaultFormatter;
    }

    /**
     * Get the default scale to use for calculations
     *
     * @return int
     */
    public static function getDefaultScale()
    {
        return self::$defaultScale;
    }

    /**
     * Constructor
     *
     * @param string $amount
     * @param string $currency ISO-4217 code
     */
    public function __construct($amount, $currency = 'USD')
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->formatter = self::getDefaultFormatter();
        $this->scale = self::getDefaultScale();
    }

    /**
     * Get the internal amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get the internal currency as an ISO-4217 code
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get the internal number formatter
     *
     * @return NumberFormatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Get the scale used in calculations for this object
     *
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * Set the internal number formatter
     *
     * @param  NumberFormatter $formatter
     * @return Money
     */
    public function setFormatter(NumberFormatter $formatter)
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * Set the scale used in calculations for this object
     *
     * @param  int   $value
     * @return Money
     */
    public function setScale($value)
    {
        $this->scale = (int) $value;

        return $this;
    }

    /**
     * Add a number or Money to this object and return a new Money result
     *
     * If you supply a non-Money value, it will be checked to verify that it
     * looks like a number, and automatically converted to a Money object.
     *
     * @param  mixed                    $other
     * @throws InvalidArgumentException if an invalid value is supplied
     */
    public function add($other)
    {
        return $this->operation('bcadd', $other);
    }

    /**
     * Divide a number or Money into this object and return a new Money result
     *
     * If you supply a non-Money value, it will be checked to verify that it
     * looks like a number, and automatically converted to a Money object.
     *
     * @param  mixed                    $other
     * @throws InvalidArgumentException if an invalid value is supplied
     */
    public function div($other)
    {
        return $this->operation('bcdiv', $other);
    }

    /**
     * Multiply a number or Money by this object and return a new Money result
     *
     * If you supply a non-Money value, it will be checked to verify that it
     * looks like a number, and automatically converted to a Money object.
     *
     * @param  mixed                    $other
     * @throws InvalidArgumentException if an invalid value is supplied
     */
    public function mul($other)
    {
        return $this->operation('bcmul', $other);
    }

    /**
     * Subtract a number or Money from this object and return a new Money result
     *
     * If you supply a non-Money value, it will be checked to verify that it
     * looks like a number, and automatically converted to a Money object.
     *
     * @param  mixed                    $other
     * @throws InvalidArgumentException if an invalid value is supplied
     */
    public function sub($other)
    {
        return $this->operation('bcsub', $other);
    }

    /**
     * Round the internal amount at the specified precision
     *
     * Note that the value returned is a string. This is a function of the BC
     * library and allows large numbers to be represented.
     *
     * @param  int    $precision
     * @return string
     */
    public function round($precision)
    {
        $amount = $this->getAmount();

        if (false !== strpos($amount, '.')) {
            if (bccomp($amount, '0', $this->getScale()) < 0) {
                $function = 'bcsub';
            } else {
                $function = 'bcadd';
            }

            return $function(
                $amount,
                sprintf(
                    '0.%s5',
                    str_repeat('0', $precision)
                ),
                $precision
            );
        }

        return $amount;
    }

    /**
     * Format the number and currency according to the current formatter
     *
     * For example, if the currency is USD and the value is 42.123, this will
     * output "$42.12".
     *
     * @return string
     */
    public function format()
    {
        return $this->getFormatter()->formatCurrency(
            $this->getAmount(),
            $this->getCurrency()
        );
    }

    /**
     * Format the Money object according to {@see format()}
     *
     * @see format()
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * Asserts the validity of a given value and converts it to a Money object
     *
     * @param  mixed                    $money
     * @return Money
     * @throws InvalidArgumentException if a value is not valid money
     */
    protected function assertAndConvertValidMoney($money)
    {
        if (is_numeric($money)) {
            $money = new Money(
                $money,
                $this->getCurrency(),
                $this->getScale()
            );
        } else {
            if (!$money instanceof Money) {
                throw new InvalidArgumentException(
                    'Value must be either numeric or an instance of Money'
                );
            }
        }

        if ($this->getCurrency() !== $money->getCurrency()) {
            throw new InvalidArgumentException(
                'Value must be of the same currency as ' . $this->getCurrency()
            );
        }

        return $money;
    }

    /**
     * Generic function to perform a BC math operation
     *
     * @param  string $func  a valid BC math function
     * @param  mixed  $other second value to use in the operation
     * @return Money
     */
    protected function operation($func, $other)
    {
        $other = $this->assertAndConvertValidMoney($other);

        return new Money(
            $func(
                $this->getAmount(),
                $other->getAmount(),
                $this->getScale()
            ),
            $this->getCurrency(),
            $this->getScale()
        );
    }
}
