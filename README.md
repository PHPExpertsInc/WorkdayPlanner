# The PHP Workday Planner

## Installation

Run `composer require phpexperts/workday-planner`

## The Problem

Imagine that you have to schedule a task to run every workday in your country 
over a given time period.

In order to do this, you first have to figure out what the workdays are, and
the weekends. Simple enough, right? But then you remember: "Hey, what about
Christmas? and all those other holidays?" 

So you dutifully go about detecting those, too. Only to have your clients
complaining on the Monday after the Fourth of July. Because, you see, at least
in the United States, holidays that occur on Saturdays are observed on the 
previous Friday, while holidays that occur on Sundays are observed on the next
Monday.

## The Solution

With this package, all of these technicalities are handled for you. You give it
a date range, and it will dutifully spit out the workdays, keeping in mind both
the actual and observed U.S. national holidays common to most employers.

## Ad-hoc Workday Verification

```php
    use PHPExperts\WorkdayPlanner\WorkdayDetector;
    
    function isWorkday($dateString) {
        $isWorkday = WorkdayDetector::isWorkday(new \DateTime($dateString));
        $isOrIsnt = $isWorkday ? 'is' : 'is not';

        echo "$dateString $isOrIsnt a work day.\n";
    }
    
    // Is Friday, 10 August 2018, a workday?
    isWorkday('10 August 2018');    // Workday
    isWorkday('11 August 2018');    // Weekend
    isWorkday('25 December 2018');  // Fixed holiday
    isWorkday('22 November 2018');  // Floating holiday
    
     /* Output:
     10 August 2018 is a work day.
     11 August 2018 is not a work day.
     25 December 2018 is not a work day.
     22 November 2018 is not a work day. 
     */
```

## Floating Holidays

Some holidays are based upon a certain day of the month, instead of a fixed date.
These are called "floating holidays". 

The date spec looks like this for Thanksgiving Day:
```json
  {
    "name": "Thanksgiving Day",
    "type": "day",
    "when": "fourth Thursday of November"
  }
```
Examples:
```php
    WorkdayDetector::isWorkday(new \DateTime('2018-11-22')); // false; Thanksgiving Day
    WorkdayDetector::isWorkday(new \DateTime('2019-11-28')); // false; Thanksgiving Day

    echo (new HolidayDetector())         // 2018-11-22
        ->getHoliday('Thanksgiving Day')
        ->format('Y-m-d');

    echo (new HolidayDetector())         // 2018-11-28
        ->changeYear(2019)
        ->getHoliday('Thanksgiving Day')
        ->format('Y-m-d');
```

### Observerable Holidays

```php
    // The Fourth of July occurs on a Sunday in 2021, so the following Monday is not
    // a workday.
    
    use PHPExperts\WorkdayPlanner\WorkdayPlanner;
    
    $planner = new WorkdayPlanner(new \DateTime('2021-07-01'), new \DateTime('2021-07-06'));
    
    echo json_encode(
        $planner->getWorkdays(), 
        JSON_PRETTY_PRINT
    );
    
    /* Output:
    [
        "2021-07-01",
        "2021-07-02",
        "2021-07-06"
    ]
    */
```

### Ad-hoc Holiday Verification

```php
    $detector = new HolidayDetector();
    var_dump([
        $detector->isHoliday('2021-07-04'), // The actual holiday.
        $detector->isHoliday('2021-07-05'), // The observed holiday
    ]);
    
    $detector = new HolidayDetector();
    $detector->changeYear(2021);
    print_r([
        'actual' => $detector->getHoliday('Independence Day')->format('l jS \of F Y'),
        'observed' => $detector->getHoliday('Independence Day (Observed)')->format('l jS \of F Y'),
    ]);

    /* Output:
    array(2) {
      [0] => bool(true)
      [1] => bool(true)
    }
    
    Array
    (
        [actual] => Sunday 4th of July 2021
        [observed] => Monday 5th of July 2021
    )
    */
```

## Access to the Workdays

There are several ways to access the workdays. 

The Workday Planner can be accessed like an array, or as an iterator, or you
can just get a simple array of all the workday dates.

### Simple access

```php
    $planner = new WorkdayPlanner(new \DateTime('2021-07-01'), new \DateTime('2021-07-06'));
    
    echo json_encode(
        $planner->getWorkdays(), 
        JSON_PRETTY_PRINT
    );
    
    /* Output:
    [
        "2021-07-01",
        "2021-07-02",
        "2021-07-06"
    ]
    */
```

### As an iterator

```php
    $planner = new WorkdayPlanner(new \DateTime('2021-07-01'), new \DateTime('2021-07-06'));
    /** @var \DateTime $workday */
    foreach ($planner as $workday) {
        // ...
    }
```

### As an array

```php
    $planner = new WorkdayPlanner(new \DateTime('2021-07-01'), new \DateTime('2021-07-06'));

    // Is it a workday?
    if (isset($planner['2021-07-01'])) {
        echo "Yes, it is.\n";
    }
    else {
        echo "No, it is not.\n";
    }
    
    // Since '2021-07-01' is the first day in the range, it is equal to $planner[0].
    echo ($planner[0] === $planner['2021-07-01']) ? 'Strictly equal!' : 'Not equal.';
    
    /* Output:
    Yes, it is.
    Strictly equal!
    */
```

### Manually remove a workday

Workdays can be manually removed, per your policies:

```php
    $planner = new WorkdayPlanner(new \DateTime('2021-07-01'), new \DateTime('2021-07-06'));
    unset($planner['2021-07-01']);

    // Is it a workday?
    if (isset($planner['2021-07-01'])) {
        echo "Yes, it is.\n";
    }
    else {
        echo "No, it is not.\n";
    }

    /* Output:
    No, it is not.
    */
```

## Behavior Documentation

The project currently has 100% code coverage via unit tests.

Here is the behavior documentation, generated by PHPUnit's testdox:

### HolidayDetector
  1. Can parse fixed holiday dates
  2. Can parse floating holiday dates
  3. Can determine if a date is a holiday
  4. Shows error for unimplemented country
  5. Shows error for invalid data
  6. Shows error for invalid holiday spec
  7. Properly handles unknown holidays
  8. Will report friday as the observed day for saturday holidays
  9. Will report friday as the observed day for sunday holidays

### WorkdayDetector
  1. Can determine if a date is a workday

### WorkdayPlanner
  1. Will create a date range of workdays
  2. Will properly offset saturday holidays
  3. Will properly offset sunday holidays
  4. Can iterate through each date
  5. The number of workdays is countable
  6. Can access dates via the array operator with a numeric index
  7. Can access dates via the array operator with a date index
  8. Can use numeric isset on a workday
  9. Can use date isset on a workday
 10. Can remove a work day via unset
 11. Properly handles a start date later than the end date
 12. Properly handles a start date equal to the end date
 13. Properly handles invalid dates
 14. Wont allow manually adding workdays

# Credits

Created by Theodore R. Smith <theodore@phpexperts.pro>, mostly in one day.

A [PHP Experts, Inc.](https://www.phpexperts.pro/), project.
