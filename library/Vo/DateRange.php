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

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Class to deal with and perform operations on ranges of dates.
 *
 * @link http://www.martinfowler.com/eeaDev/Range.html
 */
class DateRange
{
    /**
     * Far-future ISO-8601 date
     *
     * @var string
     */
    const FUTURE = '9999-12-31';

    /**
     * Far-past ISO-8601 date
     *
     * @var string
     */
    const PAST = '1000-01-01';

    /**
     * Range start date
     *
     * @var DateTimeImmutable
     */
    protected $start;

    /**
     * Range end date
     *
     * @var DateTimeImmutable
     */
    protected $end;

    /**
     * Create a DateRange from a start date and an end date
     *
     * @param DateTimeInterface $start Start date
     * @param DateTimeInterface $end   End date
     */
    public function __construct(DateTimeInterface $start, DateTimeInterface $end)
    {
        $this->start = self::ensureDateTimeImmutable($start);
        $this->end = self::ensureDateTimeImmutable($end);
    }

    /**
     * Build a DateRange object from an ISO-8601 interval string
     *
     * Currently, this only accepts a start date and end date. Durations are not
     * supported. In addition, if any elements are missing from the end value,
     * these are not assumed to be the same as for the start value.
     *
     * @param  string    $string ISO-8601 interval string
     * @return DateRange
     */
    public static function fromIso8601($string)
    {
        $split = explode('/', $string, 2);

        if (count($split) < 2) {
            throw new InvalidArgumentException(
                'The format is expected to be two ISO-8601 dates separated by'
                . ' a \'/\'.'
            );
        }

        return new static(
            new DateTimeImmutable($split[0]),
            new DateTimeImmutable($split[1])
        );
    }

    /**
     * Build a DateRange object from existing data
     *
     * This accepts an array or object and assumes members 'start' and 'end'
     * somewhere in the array or object. You can override these values with
     * whatever you like.
     *
     * <pre>
     * // Example usage
     * $array = array(
     *     'start' => '2009-05-06',
     *     'end' => new DateTimeImmutable('2009-06-07')
     * );
     *
     * $object = new stdClass();
     * $object->start = '2009-05-06';
     * $object->end = new DateTimeImmutable('2009-06-07');
     *
     * $range1 = DateRange::fromData($array);
     * $range2 = DateRange::fromData($object);
     * </pre>
     *
     * @param  array|object $data
     * @param  string       $startIdx 'Start' member or index name
     * @param  string       $endIdx   'End' member or index name
     * @return DateRange
     */
    public static function fromData($data, $startIdx = 'start', $endIdx = 'end')
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        } elseif (!is_array($data)) {
            throw new InvalidArgumentException(
                'You must pass either an array or an object as the first parameter.'
            );
        }

        $start = $end = null;

        if (isset($data[$startIdx])) {
            if ($data[$startIdx] instanceof DateTimeInterface) {
                $start = self::ensureDateTimeImmutable($data[$startIdx]);
            } else {
                $start = new DateTimeImmutable($data[$startIdx]);
            }
        }

        if (isset($data[$endIdx])) {
            if ($data[$endIdx] instanceof DateTimeInterface) {
                $end = self::ensureDateTimeImmutable($data[$endIdx]);
            } else {
                $end = new DateTimeImmutable($data[$endIdx]);
            }
        }

        if (is_null($start) && is_null($end)) {
            $range = static::infinite();
        } elseif (is_null($start)) {
            $range = static::upTo($end);
        } elseif (is_null($end)) {
            $range = static::startingOn($start);
        } else {
            $range = new static($start, $end);
        }

        return $range;
    }

    /**
     * Create the infinite date range
     *
     * Note: internally, a finite but unusual boundary is used.
     *
     * @return DateRange
     */
    public static function infinite()
    {
        return new static(
            new DateTimeImmutable(static::PAST),
            new DateTimeImmutable(static::FUTURE)
        );
    }

    /**
     * Create a date range with an unbounded past, but a bounded future
     *
     * @param  DateTimeInterface $end Upper bound
     * @return DateRange
     */
    public static function upTo(DateTimeInterface $end)
    {
        return new static(
            new DateTimeImmutable(static::PAST),
            $end
        );
    }

    /**
     * Create a date range with an bounded past, but an unbounded future
     *
     * @param  DateTimeInterface $start Lower bound
     * @return DateRange
     */
    public static function startingOn(DateTimeInterface $start)
    {
        return new static(
            $start,
            new DateTimeImmutable(static::FUTURE)
        );
    }

    /**
     * Accessor that returns the start date of this range
     *
     * @return DateTimeImmutable
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Accessor that returns the end date of this range
     *
     * @return DateTimeImmutable
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Test whether this range represents an empty range
     *
     * This is primarily used internally, but other methods may set the range
     * to empty. This usually signals some kind of error where the return value
     * is expected to be a DateRange and can be tested for emptiness.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getEnd() < $this->getStart();
    }

    /**
     * Test a DateRange for equality with the current DateRange
     *
     * @param  DateRange $arg Other DateRange to test
     * @return bool
     */
    public function equals(DateRange $arg)
    {
        return $this->getStart() == $arg->getStart()
            && $this->getEnd() == $arg->getEnd();
    }

    /**
     * Test whether this DateRange includes a DateTimeInterface or a DateRange
     *
     * If a DateTimeInterface is greater than or equal to the start of AND less
     * than or equal to the end of this DateRange, it is considered included.
     *
     * If a DateRange is fully enclosed inside this DateRange, it is considered
     * included. The test is essentially the same as for the DateTimeInterface
     * except it is performed on both the start and end dates of the DateRange.
     *
     * @param  DateTimeInterface|DateRange $arg Other object to test
     * @return bool
     */
    public function includes($arg)
    {
        if ($arg instanceof DateTimeInterface) {
            return $this->getStart() <= $arg
                && $this->getEnd() >= $arg;
        } elseif ($arg instanceof DateRange) {
            return $this->includes($arg->getStart())
                && $this->includes($arg->getEnd());
        } else {
            throw new InvalidArgumentException(
                'Argument must be an instance of DateTimeInterface or'
                . ' ' . __CLASS__
            );
        }
    }

    /**
     * Test whether this DateRange overlaps the current DateRange
     *
     * @param  DateRange $arg Other DateRange to test
     * @return bool
     */
    public function overlaps(DateRange $arg)
    {
        return $arg->includes($this->getStart())
            || $arg->includes($this->getEnd())
            || $this->includes($arg);
    }

    /**
     * Test whether this date range has a gap and if so, of how many days
     *
     * This function will return false if the date ranges overlap.
     *
     * @param  DateRange $arg Other DateRange to test
     * @return false|int
     */
    public function gap(DateRange $arg)
    {
        if ($this->overlaps($arg)) {
            return false;
        }

        if ($this->compareTo($arg) < 0) {
            $lower = $this;
            $higher = $arg;
        } else {
            $lower = $arg;
            $higher = $this;
        }

        $interval = date_diff(
            $higher->getStart(),
            $lower->getEnd()
        );

        return $interval->format('%a') - 1;
    }

    /**
     * Test if the date ranges are next to each other and non-overlapping
     *
     * @param  DateRange $arg Other DateRange to test
     * @return bool
     */
    public function abuts(DateRange $arg)
    {
        return !$this->overlaps($arg)
            && $this->gap($arg) === 0;
    }

    /**
     * Take the difference of two date ranges
     *
     * The difference of two date ranges in this case means that the overlap of
     * the two ranges will be removed from the first range, and the result will
     * be returned.
     *
     * This method will refuse to bisect the current date range (thus,
     * confusingly, creating two date ranges), so the argument date range must
     * begin prior to and end during the current date range, or begin during and
     * end after the current date range.
     *
     * @param  DateRange           $arg Other DateRange to test
     * @return DateRange
     * @throws OutOfRangeException
     */
    public function diff(DateRange $arg)
    {
        if (!$this->overlaps($arg)) {
            throw new OutOfRangeException('Argument must overlap this range');
        }

        if ($this->getStart() < $arg->getStart()
            && $this->getEnd() > $arg->getEnd()
        ) {
            throw new OutOfRangeException('Argument must not be exclusively contained within this range');
        }

        if ($this->getStart() < $arg->getStart()) {
            return new static(
                $this->getStart(),
                $arg->getStart()->modify('-1 day')
            );
        }

        return new static(
            $arg->getEnd()->modify('+1 day'),
            $this->getEnd()
        );
    }

    /**
     * Test if a series of DateRanges are contiguous
     *
     * In other words, test that each of the date ranges 'abut' one another.
     *
     * @param  array $args Other DateRanges to test
     * @return bool
     */
    public static function isContiguous(array $args)
    {
        usort(
            $args,
            function ($a, $b) {
                return $a->compareTo($b);
            }
        );

        for ($i = 0; $i < count($args) - 1; $i++) {
            if (!$args[$i]->abuts($args[$i + 1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return the start of a series of DateRanges
     *
     * @param  array    $args    Other DateRanges to test
     * @return DateTimeImmutable
     */
    public static function getSeriesStart(array $args)
    {
        $starts = array();
        foreach ($args as $arg) {
            $starts[] = $arg->getStart();
        }

        return min($starts);
    }

    /**
     * Return the end of a series of DateRanges
     *
     * @param  array    $args    Other DateRanges to test
     * @return DateTimeImmutable
     */
    public static function getSeriesEnd(array $args)
    {
        $ends = array();
        foreach ($args as $arg) {
            $ends[] = $arg->getEnd();
        }

        return max($ends);
    }

    /**
     * A comparison function for two DateRanges
     *
     * Returns either -1, 0, or 1 if the current date range is less than, equal
     * to, or greater than the tested date range.
     *
     * @param  DateRange $arg Other DateRange to test
     * @return int
     */
    public function compareTo(DateRange $arg)
    {
        if ($this->equals($arg)) {
            return 0;
        }

        if ($this->getStart() != $arg->getStart()) {
            return $this->getStart() < $arg->getStart() ? -1 : 1;
        }

        return $this->getEnd() < $arg->getEnd() ? -1 : 1;
    }

    /**
     * Convert the DateRange to an ISO-8601 interval string
     *
     * http://en.wikipedia.org/wiki/ISO_8601#Time_intervals
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->isEmpty()) {
            return '';
        }

        return implode(
            '/',
            array(
                $this->getStart()->format('Y-m-d'),
                $this->getEnd()->format('Y-m-d')
            )
        );
    }

    /**
     * Determine if the range is anchored in the future.
     *
     * @return bool
     */
    public function isFuture()
    {
        return new DateTimeImmutable(static::FUTURE) == $this->getEnd();
    }

    /**
     * Determine if the range is anchored in the past.
     *
     * @return bool
     */
    public function isPast()
    {
        return new DateTimeImmutable(static::PAST) == $this->getStart();
    }

    /**
     * Determine if the range is infinite.
     *
     * @return bool
     */
    public function isInfinite()
    {
        return static::infinite() == $this;
    }

    /**
     * Ensure a DateTimeInterface is an instance of DateTimeImmutable
     *
     * The object will be converted, if necessary.
     *
     * @param  DateTimeInterface $d Date to convert
     * @return DateTimeImmutable
     */
    private static function ensureDateTimeImmutable(DateTimeInterface $d)
    {
        if ($d instanceof DateTimeImmutable) {
            return $d;
        }

        return DateTimeImmutable::createFromMutable($d);
    }
}
