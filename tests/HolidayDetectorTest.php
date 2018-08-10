<?php declare(strict_types=1);

/**
 * This file is part of the Workday Planner, a PHP Experts, Inc., Project.
 *
 * Copyright © 2018 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *  GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *  https://www.phpexperts.pro/
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\WorkdayPlanner\Tests;

use PHPExperts\WorkdayPlanner\HolidayDetector;
use PHPExperts\WorkdayPlanner\WorkdayPlanner;
use PHPUnit\Framework\TestCase;

class HolidayDetectorTest extends TestCase
{
    /** @var HolidayDetector */
    protected $detector;

    public function setUp()
    {
        $this->detector = new HolidayDetector('us');

        parent::setUp();
    }

    /**
     * @covers \PHPExperts\WorkdayPlanner\HolidayDetector
     */
    public function testCanParseFixedHolidayDates()
    {
        $fixedHoliday = json_decode('
{
    "name": "St. Patrick\'s Day",
    "type": "date",
    "when": "04-17"
}');

        $this->detector->addHoliday($fixedHoliday);

        $year = date('Y');
        $this->assertTrue(
            $this->detector->isHoliday(new \DateTime($year.'-04-17')),
            'Did not successfully load the St. Patrick\'s Day data.'
        );
    }

    /**
     * @covers \PHPExperts\WorkdayPlanner\HolidayDetector
     */
    public function testCanParseFloatingHolidayDates()
    {
        $floatingHoliday = json_decode('
{
    "name": "Martin Luther King Jr Day",
    "type": "day",
    "when": "third Monday of January"
}');

        $years = [
            '2018' => '2018-01-15',
            '2019' => '2019-01-21',
            '2020' => '2020-01-20',
        ];

        foreach ($years as $year => $expectedDate) {
            $detector = new HolidayDetector('us');
            $detector->changeYear($year);
            $detector->addHoliday($floatingHoliday);

            $this->assertTrue(
                $detector->isHoliday(new \DateTime($expectedDate)),
                "Did not successfully load the flexible holiday data: ($expectedDate)."
            );

            $this->assertEquals(
                $expectedDate,
                $detector->getHoliday('Martin Luther King Jr Day')->format('Y-m-d'),
                'Retrieved the wrong date for Martin Luther King Jr Day.'
            );
        }
    }

    /**
     * @covers \PHPExperts\WorkdayPlanner\HolidayDetector
     */
    public function testCanDetermineIfADateIsAHoliday()
    {
        $nonHolidays = [
            '2018-08-06', '2018-08-10', '2018-11-21',
            '2018-07-28', '2018-08-05', '2018-12-23',
            '2032-07-03',
        ];
        $holidays = [
            // Easy holidays:
            '2018-01-01', '2018-11-22',
            // Hard holidays:
            '2032-07-05', '2021-12-24',

        ];

        foreach ($nonHolidays as $day) {
            $this->assertFalse(
                $this->detector->isHoliday(new \DateTime($day)),
                "A non-holiday($day) was detected as a holiday."
            );
        }

        foreach ($holidays as $day) {
            $this->assertTrue(
                $this->detector->isHoliday(new \DateTime($day)),
                "A holiday ($day) was detected as a non-holiday."
            );
        }
    }

    public function testShowsErrorForUnimplementedCountry()
    {
        $this->expectException(\LogicException::class);

        new HolidayDetector('nonexistant');
    }

    public function testShowsErrorForInvalidData()
    {
        $invalidFile = realpath(__DIR__.'/../data/holidays') . '/invalid.json';
        file_put_contents($invalidFile, 'invalid JSON');

        try {
            new HolidayDetector('invalid');
            $this->fail('Did not throw an error with invalid JSON.');
        } catch (\RuntimeException $e) {
            unlink($invalidFile);
            $this->assertEquals("Invalid holiday data for 'invalid'.", $e->getMessage());
        }
    }

    public function testShowsErrorForInvalidHolidaySpec()
    {
        $this->expectException(\LogicException::class);

        $specJSON = <<<JSON
{
    "name": "Mars Day",
    "type": "martian",
    "when": "first Monday of September"

}
JSON;

        $this->detector->addHoliday(json_decode($specJSON));
    }

    public function testProperlyHandlesUnknownHolidays()
    {
        $this->assertNull($this->detector->getHoliday('Unknown Holiday'));
    }

    public function testWillReportFridayAsTheObservedDayForSaturdayHolidays()
    {
        // 2016-12-24 is on a Saturday.
        $this->assertTrue($this->detector->isHoliday(new \DateTime('2016-12-24')));
        $this->assertTrue($this->detector->isHoliday(new \DateTime('2016-12-23')));
        $this->assertEquals(new \DateTime('2016-12-24'), $this->detector->getHoliday('Christmas Eve'));
        $this->assertEquals(new \DateTime('2016-12-23'), $this->detector->getHoliday('Christmas Eve (Observed)'));
    }

    public function testWillReportFridayAsTheObservedDayForSundayHolidays()
    {
        // 2021-07-04 is on a Sunday.
        $this->assertTrue($this->detector->isHoliday(new \DateTime('2021-07-04')));
        $this->assertTrue($this->detector->isHoliday(new \DateTime('2021-07-05')));
        $this->assertEquals(new \DateTime('2021-07-04'), $this->detector->getHoliday('Independence Day'));
        $this->assertEquals(new \DateTime('2021-07-05'), $this->detector->getHoliday('Independence Day (Observed)'));
    }
}
