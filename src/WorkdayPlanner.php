<?php declare(strict_types=1);

namespace PHPExperts\WorkdayPlanner;

class WorkdayPlanner implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /** @var \DateTime[] */
    protected $workdays;

    /** @var int */
    private $numberOfWorkdays = 0;

    public function __construct(\DateTime $startDate, \DateTime $endDate, int $year = null, string $country = 'us')
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
                $this->workdays[$date->format('Y-m-d')] = $date;
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
    public function offsetExists($offset)
    {
        return isset($this->workdays[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($index)
    {
        return $this->workdays[$index];
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
        if (!isset($this->workdays[$index])) {
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
        unset($this->workdays[$dateString]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        // Only iterate through the numeric indexes.
        return new \ArrayIterator(
            array_filter($this->workdays, function ($key) {
                return is_int($key);
            }, ARRAY_FILTER_USE_KEY)
        );
    }

    public function getWorkdays(string $format = 'Y-m-d'): array
    {
        $workdays = [];
        foreach ($this->workdays as $index => $date) {
            if (!is_int($index)) {
                continue;
            }

            $workdays[] = $date->format($format);
        }

        return $workdays;
    }
}
