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

use DateTime;

class HolidayDetector
{
    /** @var string */
    protected $country;

    /** @var DateTime[] */
    protected $holidays;

    /** @var DateTime[] */
    protected $holidaysByName;

    /** @var int */
    private $year;

    /** @var object */
    private $holidaySpecs = [];

    public function __construct(string $country)
    {
        $this->country = $country;
        $this->holidaySpecs = array_merge($this->holidaySpecs, $this->fetchHolidaySpecs());
        $this->changeYear((int) date('Y'));
    }

    public function changeYear(int $year)
    {
        // Bug out if the year has already been initialized.
        if ($this->year === $year) {
            return;
        }

        $this->year = $year;

        $this->loadHolidays();
    }

    protected function fetchHolidaySpecs(): array
    {
        $country = $this->country;
        $holidayFile = realpath(__DIR__ . "/../data/holidays/$country.json");
        if (empty($holidayFile) || !is_readable($holidayFile)) {
            throw new \LogicException("No accessible holiday data for '$country'.");
        }

        $holidayData = json_decode(file_get_contents($holidayFile));
        if (!$holidayData) {
            throw new \RuntimeException("Invalid holiday data for '$country'.");
        }

        return $holidayData;
    }

    public function addHoliday($spec)
    {
        $parseDate = function(string $when): DateTime {
            return new DateTime("{$this->year}-{$when}");
        };
        $parseDay = function(string $when): DateTime {
            return new DateTime("$when {$this->year}");
        };

        if ($spec->type === 'date') {
            $date = $parseDate($spec->when);
        }
        elseif ($spec->type === 'day') {
            $date = $parseDay($spec->when);
        }
        else {
            throw new \LogicException("Type '{$spec->type}' is not implemented.");
        }

        $actualDateString = $date->format('Y-m-d');

        $observedDate = $this->getWeekendHolidayObservationDate($date);
        $observedDateString = $observedDate->format('Y-m-d');

        $this->holidays[$actualDateString] = $date;
        $this->holidaysByName[$spec->name] = $date;


        if ($observedDateString !== $actualDateString) {
            $this->holidays[$observedDateString] = $observedDate;
            $this->holidaysByName[$spec->name . ' (Observed)'] = $observedDate;
        }
    }

    /**
     * The U.S., and some other countries, observe weekend holidays either on the previous Friday or the next
     * Monday, depending on whether the holiday lands on a Saturday or Sunday.
     *
     * This method determines the appropriate observation date, if different.
     *
     * @param DateTime $date
     * @return DateTime
     */
    protected function getWeekendHolidayObservationDate(DateTime $date): DateTime
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

    public function isHoliday(DateTime $date): bool
    {
        $this->changeYear((int) $date->format('Y'));

        $isoDate = $date->format('Y-m-d');

        return !empty($this->holidays[$isoDate]);
    }

    public function getHoliday(string $holidayName): ?DateTime
    {
        if (empty($this->holidaysByName[$holidayName])) {
            return null;
        }

        return $this->holidaysByName[$holidayName];
    }
}

