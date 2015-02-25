<?php
/**
 * @coversDefaultClass Proration
 */
class ProrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Proration', new Proration("2015-02-13T14:30:00-08:00", 1, 1, Proration::PERIOD_MONTH));
    }
    
    /**
     * @covers ::setTimeZone
     */
    public function testSetTimeZone()
    {
        $proration = new Proration("", 1, 1, Proration::PERIOD_MONTH);
        $this->assertInstanceOf('Proration', $proration->setTimeZone('America/New_York'));
    }
    
    /**
     * @covers ::prorateDate
     * @dataProvider prorateDateProvider
     */
    public function testProrateDate($start_date, $prorate_day, $term, $period, $time_zone, $result)
    {
        $proration = new Proration($start_date, $prorate_day, $term, $period);
        $this->assertEquals($result, $proration->setTimeZone($time_zone)->prorateDate());
    }
    
    /**
     * @covers ::canProrate
     * @dataProvider prorateDateProvider
     */
    public function testCanProrate($start_date, $prorate_day, $term, $period, $time_zone, $result)
    {
        $proration = new Proration($start_date, $prorate_day, $term, $period);
        $this->assertEquals($result !== null, $proration->setTimeZone($time_zone)->canProrate());
    }
    
    /**
     * Data provider for testProrateDate
     *
     * @return array
     */
    public function prorateDateProvider()
    {
        return array(
            // UTC to time zone
            array(
                "2015-02-13T05:00:00-00:00",
                1,
                1,
                Proration::PERIOD_MONTH,
                "America/New_York",
                "2015-03-01T00:00:00-05:00"
            ),
            // DST starts
            array(
                "2015-02-13T12:00:00-05:00",
                11,
                1,
                Proration::PERIOD_MONTH,
                "America/New_York",
                "2015-03-11T00:00:00-04:00"
            ),
            // DST ends
            array(
                "2015-10-31T12:00:00-04:00",
                1,
                1,
                Proration::PERIOD_MONTH,
                "America/New_York",
                "2015-11-01T00:00:00-05:00"
            ),
            array("2015-02-13T14:30:00-08:00", 1, 1, Proration::PERIOD_YEAR, null, "2015-03-01T00:00:00-08:00"),
            array("2015-02-13T14:30:00-08:00", 1, 1, Proration::PERIOD_MONTH, null, "2015-03-01T00:00:00-08:00"),
            array("2015-02-13T14:30:00-08:00", 31, 1, Proration::PERIOD_YEAR, null, "2015-02-28T00:00:00-08:00"),
            array("2015-03-13T14:30:00-08:00", 31, 1, Proration::PERIOD_MONTH, null, "2015-03-31T00:00:00-08:00"),
            array("2015-02-24T14:30:00-08:00", 26, 1, Proration::PERIOD_YEAR, null, "2015-02-26T00:00:00-08:00"),
            array("2015-01-31T00:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, null, "2015-02-01T00:00:00-08:00"),
            array("2015-02-01T00:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, null, null),
            array("2015-02-28T00:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, null, "2015-03-01T00:00:00-08:00"),
            array("2016-02-29T00:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, null, "2016-03-01T00:00:00-08:00"),
            array("2015-02-13T14:30:00-08:00", 1, 1, Proration::PERIOD_WEEK, null, null),
            array("2015-02-13T14:30:00-08:00", 1, 1, Proration::PERIOD_DAY, null, null),
            array("2015-02-13T14:30:00-08:00", 1, 1, Proration::PERIOD_ONETIME, null, null),
            array("2015-02-13T14:30:00-08:00", 0, 1, Proration::PERIOD_MONTH, null, null),
            array("2015-02-13T14:30:00-08:00", -1, 1, Proration::PERIOD_MONTH, null, null)
        );
    }
    
    /**
     * @covers ::prorateDays
     * @covers ::daysDiff
     * @dataProvider prorateDaysProvider
     */
    public function testProrateDays($start_date, $prorate_day, $term, $period, $result)
    {
        $proration = new Proration($start_date, $prorate_day, $term, $period);
        $this->assertEquals($result, $proration->prorateDays());
    }
    
    /**
     * Data provider for testProrateDays
     *
     * @return array
     */
    public function prorateDaysProvider()
    {
        return array(
            array("2015-02-28T00:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, 1),
            array("2015-02-28T11:59:59-08:00", 1, 1, Proration::PERIOD_MONTH, 1),
            array("2015-02-28T12:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, 1),
            array("2015-02-28T12:00:01-08:00", 1, 1, Proration::PERIOD_MONTH, 0),
            array("2015-02-28T12:00:00-08:00", 2, 1, Proration::PERIOD_MONTH, 2),

            array("2015-01-31T12:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, 1),
            array("2015-01-31T12:00:01-08:00", 1, 1, Proration::PERIOD_MONTH, 0),
            array("2015-01-31T11:59:59-08:00", 1, 1, Proration::PERIOD_YEAR, 1),
            array("2015-01-31T12:00:00-08:00", 1, 1, Proration::PERIOD_WEEK, 0),
            array("2015-01-31T12:00:00-08:00", 1, 1, Proration::PERIOD_DAY, 0),
            array("2015-01-31T12:00:00-08:00", 1, 1, Proration::PERIOD_ONETIME, 0)
        );
    }
    
    /**
     * @covers ::proratePrice
     * @covers ::daysDiff
     * @dataProvider proratePriceProvider
     */
    public function testProratePrice($start_date, $prorate_day, $term, $period, $price, $result)
    {
        $proration = new Proration($start_date, $prorate_day, $term, $period);
        $this->assertEquals($result, $proration->proratePrice($price));
    }
    
    /**
     * Data provider for testProratePrice
     *
     * @return array
     */
    public function proratePriceProvider()
    {
        return array(
            // 1 day of proration over 31 days ~= 0.0322580 * 100 = 3.2258
            array("2015-01-31T12:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, 100.0, 3.2258),
            // 30 days of proration over 31 days ~= 0.9677418 * 100 = 96.7742
            array("2015-01-02T12:00:00-08:00", 1, 1, Proration::PERIOD_MONTH, 100.0, 96.7742),
            // 29 days of proration over 31 days ~= 0.9354838 * 100 = 93.5484
            array("2015-01-02T12:00:01-08:00", 1, 1, Proration::PERIOD_MONTH, 100.0, 93.5484),
            // Bad period
            array("2015-01-02T12:00:01-08:00", 1, 1, Proration::PERIOD_DAY, 100.0, 0.0),
            array("2015-01-02T12:00:01-08:00", 1, 1, Proration::PERIOD_WEEK, 100.0, 0.0),
            array("2015-01-02T12:00:01-08:00", 1, 1, Proration::PERIOD_ONETIME, 100.0, 0.0),
            // Bad date value
            array(0, 1, 1, Proration::PERIOD_MONTH, 100.0, 0.0)
        );
    }
}
