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

use PHPExperts\WorkdayPlanner\WorkdayDetector;
use PHPUnit\Framework\TestCase;

class WorkdayDetectorTest extends TestCase
{
    /**
     * @covers \PHPExperts\WorkdayPlanner\WorkdayDetector
     */
    public function testCanDetermineIfADateIsAWorkday()
    {
        $workdays = ['2018-08-06', '2018-08-10'];
        $weekdays = ['2018-07-28', '2018-08-05'];
        $holidays = ['2018-01-01', '2018-11-22'];

        foreach ($workdays as $day) {
            $this->assertTrue(
                WorkdayDetector::isWorkday(new \DateTime($day)),
                "A workday ($day) was not detected as a workday."
            );
        }

        foreach ($weekdays as $day) {
            $this->assertFalse(
                WorkdayDetector::isWorkday(new \DateTime($day)),
                "A weekday ($day) was detected as a workday."
            );
        }

        foreach ($holidays as $day) {
            $this->assertFalse(
                WorkdayDetector::isWorkday(new \DateTime($day)),
                "A holiday ($day) was detected as a workday."
            );
        }
    }
}
