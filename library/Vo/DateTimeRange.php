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
     * Take the difference of two datetime ranges
     *
     * The difference of two datetime ranges in this case means that the overlap of
     * the two ranges will be removed from the first range, and the result will
     * be returned.
     *
     * This method will refuse to bisect the current datetime range (thus,
     * confusingly, creating two datetime ranges), so the argument datetime range must
     * begin prior to and end during the current datetime range, or begin during and
     * end after the current datetime range.
     *
     * @param DateRange|DateTimeRange $arg Other DateRange or DateTimeRange to test
     *
     * @return DateTimeRange
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
                $arg->getStart()->modify('-1 second')
            );
        }

        return new static(
            $arg->getEnd()->modify('+1 second'),
            $this->getEnd()
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
