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

namespace PHPExperts\WorkdayPlanner;

class WorkdayDetector
{
    public static function isWorkday(\DateTime $date, string $country = 'us'): bool
    {
        $isoDay = $date->format('N');

        $isWeekend = ($isoDay >= 6);
        $isHoliday = (new HolidayDetector($country))->isHoliday($date);

        return !($isWeekend || $isHoliday);
    }
}
