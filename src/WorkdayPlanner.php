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

class WorkdayPlanner implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /** @var \DateTime[] */
    protected $workdays;

    /** @var \DateTime[] */
    protected $workdaysByDate;

    /** @var int */
    private $numberOfWorkdays = 0;

    public function __construct(\DateTime $startDate, \DateTime $endDate, string $country = 'us')
    {
        if ($startDate >= $endDate) {
            throw new \LogicException('The start date needs to be before the end date.');
        }

        // Make the range inclusive.
        $endDate = clone $endDate;
        $endDate->modify('+1 day');

        // Build out all of the work days.
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
        $workdayCount = 0;
        /** @var \DateTime $date */
        foreach ($period as $date) {
            if (WorkdayDetector::isWorkday($date, $country)) {
                $this->workdays[$workdayCount++] = $date;
                $this->workdaysByDate[$date->format('Y-m-d')] = $date;
            }
        }

        $this->numberOfWorkdays = $workdayCount;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return $this->numberOfWorkdays;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($index)
    {
        if (is_string($index)) {
            return isset($this->workdaysByDate[$index]);
        }

        return isset($this->workdays[$index]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($index)
    {
        // Let PHP deal with undefined indexes in its typical fashion.
        if (is_int($index)) {
            return $this->workdays[$index];
        }
        else {
            return $this->workdaysByDate[$index];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($index, $value)
    {
        throw new \LogicException('Manually adding workdays is not allowed.');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($index)
    {
        if (!isset($this->workdays[$index]) && !isset($this->workdaysByDate[$index])) {
            return;
        }

        if (is_int($index)) {
            $numericKey = $index;
            $dateString = $this->workdays[$index]->format('Y-m-d');
        }
        else {
            // Search the workdays for the numeric key.
            $dateString = $index;
            $numericKey = array_search($dateString, $this->getWorkdays());
        }

        unset($this->workdays[$numericKey]);
        unset($this->workdaysByDate[$dateString]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        // Only iterate through the numeric indexes.
        return new \ArrayIterator($this->workdays);
    }

    public function getWorkdays(string $format = 'Y-m-d'): array
    {
        $workdays = [];
        foreach ($this->workdays as $index => $date) {
            $workdays[] = $date->format($format);
        }

        return $workdays;
    }
}
