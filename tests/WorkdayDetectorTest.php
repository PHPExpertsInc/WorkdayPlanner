<?php declare(strict_types=1);

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
