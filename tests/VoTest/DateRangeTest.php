<?php

namespace VoTest;

use DateTime;
use Vo\DateRange;

class DateRangeTest extends \PHPUnit_Framework_TestCase
{
    public function testFromIso8601()
    {
        $dr = DateRange::fromIso8601('2009-06-07/2011-05-04');

        $this->assertEquals(
            new DateTime('2009-06-07'),
            $dr->getStart()
        );

        $this->assertEquals(
            new DateTime('2011-05-04'),
            $dr->getEnd()
        );

        $this->setExpectedException('InvalidArgumentException');

        $dr = DateRange::fromIso8601('2009-06-07');
    }

    public function testFromData()
    {
        $dr1 = DateRange::fromData(
            (object)array(
                'start' => '2010-09-06',
                'end' => '2011-06-07'
            )
        );

        $dr2 = DateRange::fromData(
            array(
                'start' => '2010-09-06',
                'end' => '2011-06-07'
            )
        );

        foreach (array($dr1, $dr2) as $dr) {
            $this->assertEquals(
                new DateTime('2010-09-06'),
                $dr->getStart()
            );

            $this->assertEquals(
                new DateTime('2011-06-07'),
                $dr->getEnd()
            );
        }

        $dr3 = DateRange::fromData(
            array(
                'start' => '2010-09-07'
            )
        );

        $this->assertEquals(
            new DateTime('2010-09-07'),
            $dr3->getStart()
        );

        $this->assertEquals(
            new DateTime(DateRange::FUTURE),
            $dr3->getEnd()
        );

        $dr4 = DateRange::fromData(
            array(
                'end' => '2011-06-08'
            )
        );

        $this->assertEquals(
            new DateTime(DateRange::PAST),
            $dr4->getStart()
        );

        $this->assertEquals(
            new DateTime('2011-06-08'),
            $dr4->getEnd()
        );
    }

    public function testEquals()
    {
        $eq1 = new DateRange(
            new DateTime('2010-01-02'),
            new DateTime('2010-01-03')
        );

        $eq2 = new DateRange(
            new DateTime('2010-01-02'),
            new DateTime('2010-01-03')
        );

        $this->assertTrue($eq1->equals($eq2));
        $this->assertTrue($eq2->equals($eq1));

        $ne1 = new DateRange(
            new DateTime('2010-02-01'),
            new DateTime('2010-01-03')
        );

        $ne2 = new DateRange(
            new DateTime('2010-01-31'),
            new DateTime('2010-01-03')
        );

        $this->assertFalse($ne1->equals($ne2));
        $this->assertFalse($ne2->equals($ne1));
    }

    public function testIncludes()
    {
        $dr = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $this->assertTrue($dr->includes(new DateTime('2006-07-08')));
        $this->assertTrue($dr->includes(new DateTime('2006-08-01')));
        $this->assertFalse($dr->includes(new DateTime('2006-07-07')));

        $this->assertTrue($dr->includes(new DateRange(new DateTime('2006-07-08'), new DateTime('2006-07-10'))));
        $this->assertTrue($dr->includes(new DateRange(new DateTime('2006-07-09'), new DateTime('2006-09-05'))));
        $this->assertFalse($dr->includes(new DateRange(new DateTime('2006-07-07'), new DateTime('2006-07-08'))));
        $this->assertFalse($dr->includes(new DateRange(new DateTime('2006-07-09'), new DateTime('2006-09-06'))));
    }

    public function testOverlaps()
    {
        $dr = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $this->assertTrue($dr->overlaps(new DateRange(new DateTime('2006-07-08'), new DateTime('2006-07-10'))));
        $this->assertTrue($dr->overlaps(new DateRange(new DateTime('2006-07-09'), new DateTime('2006-09-05'))));
        $this->assertTrue($dr->overlaps(new DateRange(new DateTime('2006-07-07'), new DateTime('2006-07-08'))));
        $this->assertTrue($dr->overlaps(new DateRange(new DateTime('2006-07-09'), new DateTime('2006-09-06'))));
        $this->assertFalse($dr->overlaps(new DateRange(new DateTime('2006-07-06'), new DateTime('2006-07-07'))));
    }

    public function testGap()
    {
        $dr1 = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $dr2 = new DateRange(
            new DateTime('2006-09-09'),
            new DateTime('2006-09-15')
        );

        $this->assertEquals(
            3,
            $dr2->gap($dr1)
        );

        $this->assertEquals(
            3,
            $dr1->gap($dr2)
        );

        $dr3 = new DateRange(
            new DateTime('2006-09-04'),
            new DateTime('2006-09-15')
        );

        $this->assertFalse($dr1->gap($dr3));
        $this->assertFalse($dr3->gap($dr1));
    }

    public function testAbuts()
    {
        $dr1 = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $dr2 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );

        $this->assertTrue($dr2->abuts($dr1));
        $this->assertTrue($dr1->abuts($dr2));

        $dr3 = new DateRange(
            new DateTime('2006-09-07'),
            new DateTime('2006-09-15')
        );

        $this->assertFalse($dr1->abuts($dr3));
        $this->assertFalse($dr3->abuts($dr1));
    }

    public function testDiff()
    {
        $dr1 = new DateRange(
            new DateTime('2006-07-01'),
            new DateTime('2006-08-01')
        );

        $dr2 = new DateRange(
            new DateTime('2006-07-15'),
            new DateTime('2006-08-15')
        );

        $dr3 = new DateRange(
            new DateTime('2006-07-02'),
            new DateTime('2006-07-13')
        );

        $this->assertEquals(
            new DateRange(
                new DateTime('2006-07-01'),
                new DateTime('2006-07-14')
            ),
            $dr1->diff($dr2)
        );

        $this->assertEquals(
            new DateRange(
                new DateTime('2006-08-02'),
                new DateTime('2006-08-15')
            ),
            $dr2->diff($dr1)
        );

        $this->setExpectedException('OutOfRangeException');

        $dr1->diff($dr3);
    }

    public function testIsContiguous()
    {
        $dr1 = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $dr2 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );

        $dr3 = new DateRange(
            new DateTime('2006-09-15'),
            new DateTime('2006-09-25')
        );

        $this->assertFalse(DateRange::isContiguous(array($dr1, $dr2, $dr3)));

        $dr4 = new DateRange(
            new DateTime('2006-09-16'),
            new DateTime('2006-09-25')
        );

        $this->assertTrue(DateRange::isContiguous(array($dr1, $dr2, $dr4)));
        $this->assertTrue(DateRange::isContiguous(array($dr4, $dr1, $dr2)));
    }

    public function testSeriesStart()
    {
        $dr1 = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $dr2 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );

        $dr3 = new DateRange(
            new DateTime('2006-09-15'),
            new DateTime('2006-09-25')
        );

        $this->assertEquals(
            new DateTime('2006-07-08'),
            DateRange::getSeriesStart(array($dr1, $dr2, $dr3))
        );
    }

    public function testSeriesEnd()
    {
        $dr1 = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $dr2 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );

        $dr3 = new DateRange(
            new DateTime('2006-09-15'),
            new DateTime('2006-09-25')
        );

        $this->assertEquals(
            new DateTime('2006-09-25'),
            DateRange::getSeriesEnd(array($dr1, $dr2, $dr3))
        );
    }

    public function testCompareTo()
    {
        $dr1 = new DateRange(
            new DateTime('2006-07-08'),
            new DateTime('2006-09-05')
        );

        $dr2 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );

        $this->assertEquals(
            -1,
            $dr1->compareTo($dr2)
        );

        $this->assertEquals(
            1,
            $dr2->compareTo($dr1)
        );

        $dr2 = clone($dr1);

        $this->assertEquals(
            0,
            $dr1->compareTo($dr2)
        );
    }

    public function testToString()
    {
        $dr1 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );

        $this->assertEquals(
            '2006-09-06/2006-09-15',
            $dr1->__toString()
        );
    }

    public function testIsFutureIsPastIsInfinite()
    {
        $dr1 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime('2006-09-15')
        );

        $dr2 = new DateRange(
            new DateTime(DateRange::PAST),
            new DateTime('2006-09-15')
        );

        $dr3 = new DateRange(
            new DateTime('2006-09-06'),
            new DateTime(DateRange::FUTURE)
        );

        $dr4 = new DateRange(
            new DateTime(DateRange::PAST),
            new DateTime(DateRange::FUTURE)
        );

        $this->assertFalse($dr1->isPast(), $dr1->__toString());
        $this->assertFalse($dr1->isFuture(), $dr1->__toString());
        $this->assertFalse($dr1->isInfinite(), $dr1->__toString());

        $this->assertTrue($dr2->isPast(), $dr2->__toString());
        $this->assertFalse($dr2->isFuture(), $dr2->__toString());
        $this->assertFalse($dr2->isInfinite(), $dr2->__toString());

        $this->assertFalse($dr3->isPast(), $dr3->__toString());
        $this->assertTrue($dr3->isFuture(), $dr3->__toString());
        $this->assertFalse($dr3->isInfinite(), $dr3->__toString());

        $this->assertTrue($dr4->isPast(), $dr4->__toString());
        $this->assertTrue($dr4->isFuture(), $dr4->__toString());
        $this->assertTrue($dr4->isInfinite(), $dr4->__toString());
    }
}
