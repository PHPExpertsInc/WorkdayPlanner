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

use PHPExperts\WorkdayPlanner\WorkdayDetector;
use PHPUnit\Framework\TestCase;

class WorkdayDetectorTest extends TestCase
{
    public static function canDetermineIfADateIsAWorkdayProvider(): array
    {
        return [
            ['2018-08-06', '2018-07-28', '2018-01-01'],
            ['2018-08-10', '2018-08-05', '2018-11-22'],
        ];
    }

    /**
     * @covers \PHPExperts\WorkdayPlanner\WorkdayDetector
     * @dataProvider canDetermineIfADateIsAWorkdayProvider
     */
    public function testCanDetermineIfADateIsAWorkday($workday, $weekday, $holiday)
    {
        $this->assertTrue(
            WorkdayDetector::isWorkday(new \DateTime($workday)),
            "A workday ($workday) was not detected as a workday."
        );

        $this->assertFalse(
            WorkdayDetector::isWorkday(new \DateTime($weekday)),
            "A weekday ($weekday) was detected as a workday."
        );

        $this->assertFalse(
             WorkdayDetector::isWorkday(new \DateTime($holiday)),
             "A holiday ($holiday) was detected as a workday."
        );
    }
}
