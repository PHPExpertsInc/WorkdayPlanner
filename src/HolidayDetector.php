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

namespace PHPExperts\WorkdayPlanner;

use DateTimeImmutable;

require_once __DIR__ . '/easter_date.php';
ini_set('display_errors', '1');

class HolidayDetector
{
    /** @var string */
    protected $country;

    /** @var DateTimeImmutable[] */
    protected $holidays;

    /** @var DateTimeImmutable[] */
    protected $holidaysByName;

    /** @var int */
    private $year;

    /** @var array */
    private $holidaySpecs = [];

    public function __construct(string $country = 'us')
    {
        $this->country = $country;
        $this->holidaySpecs = array_merge($this->holidaySpecs, $this->fetchHolidaySpecs());
        $this->changeYear((int) date('Y'));
    }

    public function changeYear(int $year)
    {
        // Bug out if the year has already been initialized.
        if ($this->year === $year) {
            return $this;
        }

        $this->year = $year;

        $this->loadHolidays();

        return $this;
    }

    protected function fetchHolidaySpecs(): array
    {
        $country = $this->country;
        $holidayFile = realpath(__DIR__ . "/../data/holidays/$country.json");
        if (empty($holidayFile) || !is_readable($holidayFile)) {
            throw new \LogicException("No accessible holiday data for '$country'.");
        }

        $holidayData = json_decode((string) file_get_contents($holidayFile));
        if (!$holidayData) {
            throw new \RuntimeException("Invalid holiday data for '$country'.");
        }

        return $holidayData;
    }

    public function addHoliday($spec)
    {
        //dump($spec);
        $parseDate = function (string $when): DateTimeImmutable {
            return new DateTimeImmutable("{$this->year}-{$when}");
        };
        $parseDay = function (string $when): DateTimeImmutable {
            // Check for {weekday} following [x] days after Easter
            if (preg_match(
                '/^(?P<weekday>Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday) following (?P<days>\d+) days after Easter$/i',
                $when,
                $matches
            )) {
                return DateTimeImmutable::createFromFormat(
                    'Y-m-d',
                    date('Y-m-d', easter_date($this->year)))
                    ->modify("+{$matches['days']} days")
                    ->modify("next {$matches['weekday']}");
            }

            if (preg_match('/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)\s+following\s+(.+)$/i', $when, $matches)) {
                $weekday = $matches[1];
                $baseDateString = $matches[2];

                // Create the base date
                $date = new DateTimeImmutable($baseDateString);

                // Get the next occurrence of the desired weekday
                return $date->modify("next $weekday");
            }

            // Check for Thursday before Easter or Friday before Easter
            if (preg_match('/^(Thursday|Friday)\s+before\s+Easter$/i', $when, $matches)) {
                $weekday = strtolower($matches[1]); // Normalize to lowercase for strict comparison
                $easterDate = DateTimeImmutable::createFromFormat(
                    'Y-m-d',
                    date('Y-m-d', easter_date($this->year))
                );

                if ($easterDate === false) {
                    throw new RuntimeException("Failed to calculate Easter date for year {$this->year}");
                }

                // Calculate days to subtract (Thursday: 3 days, Friday: 2 days)
                $daysBeforeEaster = ($weekday === 'thursday') ? 3 : 2;
                return $easterDate->modify("-$daysBeforeEaster days");
            }

            return new DateTimeImmutable("$when {$this->year}");
        };

        if ($spec->type === 'date') {
            $date = $parseDate($spec->when);
        } elseif ($spec->type === 'day') {
            $date = $parseDay($spec->when);
        } else {
            throw new \LogicException("Type '{$spec->type}' is not implemented.");
        }

        $actualDateString = $date->format('Y-m-d');

        $observedDate = $this->getWeekendHolidayObservationDate($date);
        $observedDateString = $observedDate->format('Y-m-d');

        $this->holidays[$actualDateString] = $date;
        $this->holidaysByName[$spec->name] = $date;

        $this->holidays[$observedDateString] = $observedDate;
        $this->holidaysByName[$spec->name . ' (Observed)'] = $observedDate;
    }

    /**
     * The U.S., and some other countries, observe weekend holidays either on the previous Friday or the next
     * Monday, depending on whether the holiday lands on a Saturday or Sunday.
     *
     * This method determines the appropriate observation date, if different.
     *
     * @param DateTimeImmutable $date
     *
     * @return DateTimeImmutable
     */
    protected function getWeekendHolidayObservationDate(DateTimeImmutable $date): DateTimeImmutable
    {
        /** @var int $dayOfWeek The ISO-8601 numeric representation of the day of the week (1-7 = Monday-Sunday). */
        $dayOfWeek = (int) $date->format('N');

        // If it is on a Saturday, subtract by one day.
        if ($dayOfWeek === 6) {
            return (clone $date)->modify('-1 day');
        }

        // If it is on a Sunday, delay by one day.
        if ($dayOfWeek === 7) {
            return (clone $date)->modify('+1 day');
        }

        return $date;
    }

    protected function loadHolidays()
    {
        foreach ($this->holidaySpecs as $spec) {
            $this->addHoliday($spec);
        }
    }

    public function isHoliday(string $dateString): bool
    {
        $date = new \DateTimeImmutable($dateString);
        $this->changeYear((int) $date->format('Y'));

        $isoDate = $date->format('Y-m-d');

        return !empty($this->holidays[$isoDate]);
    }

    public function getHoliday(string $holidayName): ?DateTimeImmutable
    {
        if (empty($this->holidaysByName[$holidayName])) {
            return null;
        }

        return $this->holidaysByName[$holidayName];
    }
}
