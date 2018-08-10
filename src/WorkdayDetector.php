<?php declare(strict_types=1);

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
