<?php

namespace VoTest;

use DateTime;
use DateTimeZone;
use Vo\DateRange;
use Vo\DateTimeRange;

class DateTimeRangeTest extends \PHPUnit_Framework_TestCase
{
    public function testFromIso8601()
    {
        $dr = DateTimeRange::fromIso8601('2009-06-07T09:06:07Z/2011-05-04T11:05:04Z');

        $this->assertEquals(
            new DateTime('2009-06-07T09:06:07Z'),
            $dr->getStart()
        );

        $this->assertEquals(
            new DateTime('2011-05-04T11:05:04Z'),
            $dr->getEnd()
        );

        $this->setExpectedException('InvalidArgumentException');

        $dr = DateTimeRange::fromIso8601('2009-06-07T09:06:07Z');
    }

    public function testFromData()
    {
        $dr1 = DateTimeRange::fromData(
            (object)array(
                'start' => '2010-09-06T10:09:06Z',
                'end' => '2011-06-07T11:06:07Z'
            )
        );

        $dr2 = DateTimeRange::fromData(
            array(
                'start' => '2010-09-06T10:09:06Z',
                'end' => '2011-06-07T11:06:07Z'
            )
        );

        foreach (array($dr1, $dr2) as $dr) {
            $this->assertEquals(
                new DateTime('2010-09-06T10:09:06Z'),
                $dr->getStart()
            );

            $this->assertEquals(
                new DateTime('2011-06-07T11:06:07Z'),
                $dr->getEnd()
            );
        }

        $dr3 = DateTimeRange::fromData(
            array(
                'start' => '2010-09-07T10:09:07Z'
            )
        );

        $this->assertEquals(
            new DateTime('2010-09-07T10:09:07Z'),
            $dr3->getStart()
        );

        $this->assertEquals(
            new DateTime(DateTimeRange::FUTURE),
            $dr3->getEnd()
        );

        $dr4 = DateTimeRange::fromData(
            array(
                'end' => '2011-06-08T11:06:08Z'
            )
        );

        $this->assertEquals(
            new DateTime(DateTimeRange::PAST),
            $dr4->getStart()
        );

        $this->assertEquals(
            new DateTime('2011-06-08T11:06:08Z'),
            $dr4->getEnd()
        );
    }

    public function testEquals()
    {
        $eq1 = new DateTimeRange(
            new DateTime('2010-01-02T10:01:02Z'),
            new DateTime('2010-01-03T10:01:03Z')
        );

        $eq2 = new DateTimeRange(
            new DateTime('2010-01-02T10:01:02Z'),
            new DateTime('2010-01-03T10:01:03Z')
        );

        $this->assertTrue($eq1->equals($eq2));
        $this->assertTrue($eq2->equals($eq1));

        $ne1 = new DateTimeRange(
            new DateTime('2010-02-01T10:02:01Z'),
            new DateTime('2010-01-03T10:01:03Z')
        );

        $ne2 = new DateTimeRange(
            new DateTime('2010-01-31T10:01:31Z'),
            new DateTime('2010-01-03T10:01:03Z')
        );

        $this->assertFalse($ne1->equals($ne2));
        $this->assertFalse($ne2->equals($ne1));
    }

    public function testIncludes()
    {
        $dr = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $this->assertTrue($dr->includes(new DateTime('2006-07-08T06:07:08Z')));
        $this->assertTrue($dr->includes(new DateTime('2006-08-01T06:08:01Z')));
        $this->assertFalse($dr->includes(new DateTime('2006-07-07T06:07:07Z')));

        $this->assertTrue($dr->includes(new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-07-10T06:07:10Z')
        )));
        $this->assertTrue($dr->includes(new DateTimeRange(
            new DateTime('2006-07-09T06:07:09Z'),
            new DateTime('2006-09-05T06:09:05Z')
        )));
        $this->assertFalse($dr->includes(new DateTimeRange(
            new DateTime('2006-07-07T06:07:07Z'),
            new DateTime('2006-07-08T06:07:08Z')
        )));
        $this->assertFalse($dr->includes(new DateTimeRange(
            new DateTime('2006-07-09T06:07:09Z'),
            new DateTime('2006-09-06T06:09:06Z')
        )));
    }

    public function testOverlaps()
    {
        $dr = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $this->assertTrue($dr->overlaps(new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-07-10T06:07:10Z')
        )));
        $this->assertTrue($dr->overlaps(new DateTimeRange(
            new DateTime('2006-07-09T06:07:09Z'),
            new DateTime('2006-09-05T06:09:05Z')
        )));
        $this->assertTrue($dr->overlaps(new DateTimeRange(
            new DateTime('2006-07-07T06:07:07Z'),
            new DateTime('2006-07-08T06:07:08Z')
        )));
        $this->assertTrue($dr->overlaps(new DateTimeRange(
            new DateTime('2006-07-09T06:07:09Z'),
            new DateTime('2006-09-06T06:09:06Z')
        )));
        $this->assertFalse($dr->overlaps(new DateTimeRange(
            new DateTime('2006-07-06T06:07:06Z'),
            new DateTime('2006-07-07T06:07:07Z')
        )));
    }

    public function testGap()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime('2006-09-09T06:09:09Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $this->assertEquals(
            3,
            $dr2->gap($dr1)
        );

        $this->assertEquals(
            3,
            $dr1->gap($dr2)
        );

        $dr3 = new DateTimeRange(
            new DateTime('2006-09-04T06:09:04Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $this->assertFalse($dr1->gap($dr3));
        $this->assertFalse($dr3->gap($dr1));
    }

    public function testAbuts()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime('2006-09-06T06:09:06Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $this->assertTrue($dr2->abuts($dr1));
        $this->assertTrue($dr1->abuts($dr2));

        $dr3 = new DateTimeRange(
            new DateTime('2006-09-07T06:09:07Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $this->assertFalse($dr1->abuts($dr3));
        $this->assertFalse($dr3->abuts($dr1));
    }

    public function testDiff()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-01T06:07:01Z'),
            new DateTime('2006-08-01T06:08:01Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime('2006-07-15T06:07:15Z'),
            new DateTime('2006-08-15T06:08:15Z')
        );

        $dr3 = new DateTimeRange(
            new DateTime('2006-07-02T06:07:02Z'),
            new DateTime('2006-07-13T06:07:13Z')
        );

        $this->assertEquals(
            new DateTimeRange(
                new DateTime('2006-07-01T06:07:01Z'),
                new DateTime('2006-07-15T06:07:14Z')
            ),
            $dr1->diff($dr2)
        );

        $this->assertEquals(
            new DateTimeRange(
                new DateTime('2006-08-01T06:08:02Z'),
                new DateTime('2006-08-15T06:08:15Z')
            ),
            $dr2->diff($dr1)
        );

        $this->setExpectedException('OutOfRangeException');

        $dr1->diff($dr3);
    }

    public function testIsContiguous()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime('2006-09-06T06:09:06Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $dr3 = new DateTimeRange(
            new DateTime('2006-09-15T06:09:15Z'),
            new DateTime('2006-09-25T06:09:25Z')
        );

        $this->assertFalse(DateTimeRange::isContiguous(array($dr1, $dr2, $dr3)));

        $dr4 = new DateTimeRange(
            new DateTime('2006-09-16T06:09:16Z'),
            new DateTime('2006-09-25T06:09:25Z')
        );

        $this->assertTrue(DateTimeRange::isContiguous(array($dr1, $dr2, $dr4)));
        $this->assertTrue(DateTimeRange::isContiguous(array($dr4, $dr1, $dr2)));
    }

    public function testSeriesStart()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime('2006-09-06T06:09:06Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $dr3 = new DateTimeRange(
            new DateTime('2006-09-15T06:09:15Z'),
            new DateTime('2006-09-25T06:09:25Z')
        );

        $this->assertEquals(
            new DateTime('2006-07-08T06:07:08Z'),
            DateTimeRange::getSeriesStart(array($dr1, $dr2, $dr3))
        );
    }

    public function testSeriesEnd()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime('2006-09-06T06:09:06Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $dr3 = new DateTimeRange(
            new DateTime('2006-09-15T06:09:15Z'),
            new DateTime('2006-09-25T06:09:25Z')
        );

        $this->assertEquals(
            new DateTime('2006-09-25T06:09:25Z'),
            DateTimeRange::getSeriesEnd(array($dr1, $dr2, $dr3))
        );
    }

    public function testCompareTo()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-08T06:07:08Z'),
            new DateTime('2006-09-05T06:09:05Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime('2006-09-06T06:09:06Z'),
            new DateTime('2006-09-15T06:09:15Z')
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
        $dr1 = new DateTimeRange(
            new DateTime('2006-09-06T06:09:06Z'),
            new DateTime('2006-09-15T06:09:15Z')
        );

        $this->assertEquals(
            '2006-09-06T06:09:06+00:00/2006-09-15T06:09:15+00:00',
            $dr1->__toString()
        );
    }

    public function testDiffWithDateRange()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-01T06:07:01Z'),
            new DateTime('2006-08-01T06:08:01Z')
        );

        $dr2 = new DateRange(
            new DateTime('2006-07-15', new DateTimeZone('Europe/London')),
            new DateTime('2006-08-15', new DateTimeZone('Europe/London'))
        );

        $this->assertEquals('2006-07-01T06:07:01+00:00/2006-07-14T23:59:59+01:00', $dr1->diff($dr2)->__toString());
        $this->assertEquals('2006-08-02/2006-08-15', $dr2->diff($dr1)->__toString());
    }

    public function testIsFutureAndIsPast()
    {
        $dr1 = new DateTimeRange(
            new DateTime('2006-07-01T06:07:01Z'),
            new DateTime('2006-08-01T06:08:01Z')
        );

        $dr2 = new DateTimeRange(
            new DateTime(DateTimeRange::PAST),
            new DateTime('2006-08-01T06:08:01Z')
        );

        $dr3 = new DateTimeRange(
            new DateTime('2006-07-01T06:07:01Z'),
            new DateTime(DateTimeRange::FUTURE)
        );

        $dr4 = new DateTimeRange(
            new DateTime(DateTimeRange::PAST),
            new DateTime(DateTimeRange::FUTURE)
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
