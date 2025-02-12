<?php declare(strict_types=1);

/**
 * This file is part of the Workday Planner, a PHP Experts, Inc., Project.
 *
 * Copyright © 2018, 2019 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/Skeleton
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\WorkdayPlanner\Tests;

use DateTime;
use PHPExperts\WorkdayPlanner\WorkdayPlanner;
use PHPUnit\Framework\TestCase;

class WorkdayPlannerTest extends TestCase
{
    /**
     * @covers \PHPExperts\WorkdayPlanner\WorkdayPlanner
     */
    public function testWillCreateADateRangeOfWorkdays()
    {
        $expectedWorkdays = [
            /* Week 1 */ '2018-07-02', '2018-07-03', '2018-07-05', '2018-07-06',
            /* Week 2 */ '2018-07-09', '2018-07-10', '2018-07-11', '2018-07-12', '2018-07-13',
            /* Week 3 */ '2018-07-16', '2018-07-17', '2018-07-18', '2018-07-19', '2018-07-20',
            /* Week 4 */ '2018-07-23', '2018-07-24', '2018-07-25', '2018-07-26', '2018-07-27',
            /* Week 5 */ '2018-07-30', '2018-07-31',
        ];

        $startDate = new DateTime('2018-07-01');
        $endDate = new DateTime('2018-07-31');

        $planner = new WorkdayPlanner($startDate, $endDate);
        $workdays = $planner->getWorkdays();

        self::assertEquals($expectedWorkdays, $workdays);
    }

    public function testWillProperlyOffsetSaturdayHolidays()
    {
        $expectedWorkdays = [
            /* Week 1 */ '2021-12-01', '2021-12-02', '2021-12-03',
            /* Week 2 */ '2021-12-06', '2021-12-07', '2021-12-08', '2021-12-09', '2021-12-10',
            /* Week 3 */ '2021-12-13', '2021-12-14', '2021-12-15', '2021-12-16', '2021-12-17',
            /* Week 4 */ '2021-12-20', '2021-12-21', '2021-12-22', '2021-12-23',
            /* Week 5 */ '2021-12-27', '2021-12-28', '2021-12-29', '2021-12-30',
        ];

        $startDate = new DateTime('2021-12-01');
        $endDate = new DateTime('2021-12-31');

        $planner = new WorkdayPlanner($startDate, $endDate);
        $workdays = $planner->getWorkdays();

        self::assertEquals($expectedWorkdays, $workdays);
    }

    public function testWillProperlyOffsetSundayHolidays()
    {
        $expectedWorkdays = [
            /* Week 1 */ '2032-07-01', '2032-07-02',
            /* Week 2 */ '2032-07-06', '2032-07-07', '2032-07-08', '2032-07-09',
            /* Week 3 */ '2032-07-12', '2032-07-13', '2032-07-14', '2032-07-15', '2032-07-16',
            /* Week 4 */ '2032-07-19', '2032-07-20', '2032-07-21', '2032-07-22', '2032-07-23',
            /* Week 5 */ '2032-07-26', '2032-07-27', '2032-07-28', '2032-07-29', '2032-07-30',
        ];

        $startDate = new DateTime('2032-07-01');
        $endDate = new DateTime('2032-07-31');

        $planner = new WorkdayPlanner($startDate, $endDate);
        $workdays = $planner->getWorkdays();

        self::assertEquals($expectedWorkdays, $workdays);
    }

    public function testCanIterateThroughEachDate()
    {
        $expectedDates = ['2018-08-01', '2018-08-02', '2018-08-03', '2018-08-06', '2018-08-07'];

        $planner = new WorkdayPlanner(new DateTime($expectedDates[0]), new DateTime(end($expectedDates)));

        /**
         * @var int
         * @var DateTime $workday
         */
        foreach ($planner as $index => $workday) {
            self::assertInstanceOf(DateTime::class, $workday);
            self::assertEquals($expectedDates[$index], $workday->format('Y-m-d'));
        }
    }

    public function testTheNumberOfWorkdaysIsCountable()
    {
        $expectedDates = ['2018-08-01', '2018-08-02', '2018-08-03', '2018-08-06', '2018-08-07'];

        $planner = new WorkdayPlanner(new DateTime($expectedDates[0]), new DateTime(end($expectedDates)));
        self::assertEquals(count($expectedDates), count($planner));
    }

    public function testCanAccessDatesViaTheArrayOperatorWithANumericIndex()
    {
        $expectedDates = ['2018-08-01', '2018-08-02', '2018-08-03', '2018-08-06', '2018-08-07'];

        $planner = new WorkdayPlanner(new DateTime($expectedDates[0]), new DateTime(end($expectedDates)));

        for ($a = 0; $a < count($expectedDates); ++$a) {
            self::assertInstanceOf(DateTime::class, $planner[$a]);
            self::assertEquals($expectedDates[$a], $planner[$a]->format('Y-m-d'));
        }
    }

    public function testCanAccessDatesViaTheArrayOperatorWithADateIndex()
    {
        $expectedDates = ['2018-08-01', '2018-08-02', '2018-08-03', '2018-08-06', '2018-08-07'];

        $planner = new WorkdayPlanner(new DateTime($expectedDates[0]), new DateTime(end($expectedDates)));

        for ($a = 0; $a < count($expectedDates); ++$a) {
            self::assertInstanceOf(DateTime::class, $planner[$expectedDates[$a]]);
            self::assertEquals($expectedDates[$a], $planner[$expectedDates[$a]]->format('Y-m-d'));
        }
    }

    public function testCanUseNumericIssetOnAWorkday()
    {
        $expectedDates = ['2018-08-01', '2018-08-02', '2018-08-03', '2018-08-06', '2018-08-07'];

        $planner = new WorkdayPlanner(new DateTime($expectedDates[0]), new DateTime(end($expectedDates)));

        for ($a = 0; $a < count($expectedDates); ++$a) {
            self::assertTrue(isset($planner[$a]));
        }

        self::assertFalse(isset($planner[999]));
    }

    public function testCanUseDateIssetOnAWorkday()
    {
        $expectedDates = ['2018-08-01', '2018-08-02', '2018-08-03', '2018-08-06', '2018-08-07'];

        $planner = new WorkdayPlanner(new DateTime($expectedDates[0]), new DateTime(end($expectedDates)));

        for ($a = 0; $a < count($expectedDates); ++$a) {
            self::assertTrue(isset($planner[$expectedDates[$a]]));
        }

        self::assertFalse(isset($planner['2032-01-01']));
    }

    public function testCanRemoveAWorkDayViaUnset()
    {
        $expectedDates = ['2018-08-01', '2018-08-02', '2018-08-06', '2018-08-07'];

        $planner = new WorkdayPlanner(new DateTime($expectedDates[0]), new DateTime(end($expectedDates)));

        // Ensure it exists before the unset.
        self::assertNotEmpty($planner[2]);
        self::assertNotEmpty($planner['2018-08-03']);

        unset($planner['2018-08-03']);

        // Ensure neither key exists after.
        self::assertArrayNotHasKey('2018-08-03', $planner);
        self::assertArrayNotHasKey(2, $planner);

        unset($planner[0]);

        self::assertArrayNotHasKey('2018-08-01', $planner);
        self::assertArrayNotHasKey(0, $planner);

        unset($planner[99]);
    }

    public function testProperlyHandlesAStartDateLaterThanTheEndDate()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The start date needs to be before the end date.');

        $startDate = new DateTime('2011-05-01');
        $endDate = new DateTime('2011-04-01');

        new WorkdayPlanner($startDate, $endDate);
    }

    public function testProperlyHandlesAStartDateEqualToTheEndDate()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The start date needs to be before the end date.');

        $startDate = new DateTime('2011-05-01');
        $endDate = new DateTime('2011-05-01');

        new WorkdayPlanner($startDate, $endDate);
    }

    public function testProperlyHandlesInvalidDates()
    {
        $startDate = new DateTime('2018-03-01');
        $endDate = new DateTime('2018-04-31');

        $planner = new WorkdayPlanner($startDate, $endDate);

        $workdays = $planner->getWorkdays();
        self::assertEquals('2018-05-01', end($workdays));
    }

    public function testWontAllowManuallyAddingWorkdays()
    {
        $startDate = new DateTime('2018-08-01');
        $endDate = new DateTime('2018-08-02');

        foreach (['2018-08-04', 2] as $index) {
            try {
                $planner = new WorkdayPlanner($startDate, $endDate);
                $planner[$index] = new DateTime('2018-08-04');
                $this->fail('Manually adding a workday did not fail.');
            } catch (\LogicException $e) {
                self::assertEquals('Manually adding workdays is not allowed.', $e->getMessage());
            }
        }
    }
}
