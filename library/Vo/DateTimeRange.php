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

use OutOfRangeException;

/**
 * Class to deal with and perform operations on ranges of dates.
 *
 * @link http://www.martinfowler.com/eeaDev/Range.html
 */
class DateTimeRange extends DateRange
{
    /**
     * Far-future ISO-8601 date
     *
     * @var string
     */
    const FUTURE = '9999-12-31 23:59:59';

    /**
     * Far-past ISO-8601 date
     *
     * @var string
     */
    const PAST = '1000-01-01 00:00:00';

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
                clone $this->getStart(),
                date_modify(clone $arg->getStart(), '-1 second')
            );
        }

        return new static(
            date_modify(clone $arg->getEnd(), '+1 second'),
            clone $this->getEnd()
        );
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
                $this->getStart()->format('c'),
                $this->getEnd()->format('c')
            )
        );
    }
}
