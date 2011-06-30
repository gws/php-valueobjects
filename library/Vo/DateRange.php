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

use DateTime, InvalidArgumentException, OutOfRangeException;

/**
 * Class to deal with and perform operations on ranges of dates.
 *
 * @see http://www.martinfowler.com/ap2/range.html
 *
 * @category Vo
 * @package Vo
 */
class DateRange
{
    /**
     * Far-future ISO-8601 date
     *
     * @var string
     */
    const FUTURE = '9999-01-01';

    /**
     * Far-past ISO-8601 date
     *
     * @var string
     */
    const PAST = '1000-01-01';

    /**
     * Internal 'start' DateTime
     *
     * @var DateTime
     */
    protected $start;

    /**
     * Internal 'end' DateTime
     *
     * @var DateTime
     */
    protected $end;

    /**
     * Constructor
     *
     * @param $start Start date
     * @param $end End date
     * @return null
     */
    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Build a DateRange object from an ISO-8601 interval string
     *
     * Currently, this only accepts dates of the form Y-m-d/Y-m-d.
     *
     * @param string $string ISO-8601 interval string
     * @return self
     */
    public static function fromIso8601($string)
    {
        $split = explode('/', $string, 2);

        if (count($split) < 2) {
            throw new InvalidArgumentException(
                'The format is expected to be Y-m-d/Y-m-d.'
            );
        }

        return new self(
            new DateTime($split[0]),
            new DateTime($split[1])
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
     * $array = array('start' => '2009-05-06', 'end' => new DateTime('2009-06-07'));
     *
     * $object = new stdClass();
     * $object->start = '2009-05-06';
     * $object->end = new DateTime('2009-06-07');
     *
     * $range1 = self::fromData($array);
     * $range2 = self::fromData($object);
     * </pre>
     *
     * @param array|stdClass $object
     * @param string $start 'Start' member or index name
     * @param string $end 'End' member or index name
     * @return self
     */
    public static function fromData($object, $start = 'start', $end = 'end')
    {
        if (is_object($object)) {
            $is_object = true;
        } elseif (is_array($object)) {
            $is_object = false;
        } else {
            throw new InvalidArgumentException(
                'You must pass either an array or an object as the first parameter.'
            );
        }

        $start_dt = null;
        $end_dt = null;
        if ($is_object) {
            if (isset($object->{$start})) {
                if ($object->{$start} instanceof DateTime) {
                    $start_dt = clone $object->{$start};
                } else {
                    $start_dt = new DateTime($object->{$start});
                }
            }

            if (isset($object->{$end})) {
                if ($object->{$end} instanceof DateTime) {
                    $end_dt = clone $object->{$end};
                } else {
                    $end_dt = new DateTime($object->{$end});
                }
            }
        } else {
            if (isset($object[$start])) {
                if ($object[$start] instanceof DateTime) {
                    $start_dt = clone $object[$start];
                } else {
                    $start_dt = new DateTime($object[$start]);
                }
            }

            if (isset($object[$end])) {
                if ($object[$end] instanceof DateTime) {
                    $end_dt = clone $object[$end];
                } else {
                    $end_dt = new DateTime($object[$end]);
                }
            }
        }

        if (is_null($start_dt) && is_null($end_dt)) {
            $date_range = self::infinite();
        } elseif (is_null($start_dt)) {
            $date_range = self::upTo($end_dt);
        } elseif (is_null($end_dt)) {
            $date_range = self::startingOn($start_dt);
        } else {
            $date_range = new self($start_dt, $end_dt);
        }

        return $date_range;
    }

    /**
     * Create the infinite date range
     *
     * Note: internally, a finite but unusual boundary is used.
     *
     * @return self
     */
    public static function infinite()
    {
        return new self(new DateTime(self::PAST), new DateTime(self::FUTURE));
    }

    /**
     * Create a date range with an unbounded past, but a bounded future
     *
     * @param $end Upper bound
     * @return self
     */
    public static function upTo(DateTime $end)
    {
        return new self(new DateTime(self::PAST), $end);
    }

    /**
     * Create a date range with an bounded past, but an unbounded future
     *
     * @param $start Lower bound
     * @return self
     */
    public static function startingOn(DateTime $start)
    {
        return new self($start, new DateTime(self::FUTURE));
    }

    /**
     * Accessor that returns the start date of this range
     *
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Accessor that returns the end date of this range
     *
     * @return DateTime
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
     * @param $arg Other DateRange to test
     * @return bool
     */
    public function equals(self $arg)
    {
        return $this->getStart() == $arg->getStart()
            && $this->getEnd() == $arg->getEnd();
    }

    /**
     * Test whether this DateRange includes a DateTime or a DateRange
     *
     * If a DateTime is greater than or equal to the start of AND less than
     * or equal to the end of this DateRange, it is considered included.
     *
     * If a DateRange is fully enclosed inside this DateRange, it is
     * considered included. The test is essentially the same as for the
     * DateTime except it is performed on both the start and end dates of the
     * DateRange.
     *
     * @param DateTime|self $arg Other object to test
     * @return bool
     */
    public function includes($arg)
    {
        if ($arg instanceof DateTime) {
            return $this->getStart() <= $arg
                && $this->getEnd() >= $arg;
        } elseif ($arg instanceof self) {
            return $this->includes($arg->getStart())
                && $this->includes($arg->getEnd());
        } else {
            throw new InvalidArgumentException(
                'Argument must be an instance of DateTime or ' . __CLASS__
            );
        }
    }

    /**
     * Test whether this DateRange overlaps the current DateRange
     *
     * @param $arg Other DateRange to test
     * @return bool
     */
    public function overlaps(self $arg)
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
     * @param $arg Other DateRange to test
     * @return bool|int
     */
    public function gap(self $arg)
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
     * @param $arg Other DateRange to test
     * @return bool
     */
    public function abuts(self $arg)
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
     * @param $arg Other DateRange to test
     * @return self
     * @throws OutOfRangeException
     */
    public function diff(self $arg)
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
            return new self(
                clone $this->getStart(),
                date_modify(clone $arg->getStart(), '-1 day')
            );
        }

        return new self(
            date_modify(clone $arg->getEnd(), '+1 day'),
            clone $this->getEnd()
        );
    }

    /**
     * Test if a series of DateRanges are contiguous
     *
     * In other words, test that each of the date ranges 'abut' one another.
     *
     * @param $args Other DateRanges to test
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
     * @param $args Other DateRanges to test
     * @return DateTime
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
     * @param $args Other DateRanges to test
     * @return DateTime
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
     * @param $arg Other DateRange to test
     * @return int
     */
    public function compareTo(self $arg)
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
}
