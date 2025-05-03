<?php

namespace Core\Support\Facades;

use Core\Foundation\Facade;

/**
 * @method static \Core\Support\Date now()
 * @method static \Core\Support\Date createFromFormat(string $format, string $time, $timezone = null)
 * @method static \Core\Support\Date setTimezone(string|DateTimeZone $timezone)
 * @method static DateTimeZone getTimezone()
 * @method static \Core\Support\Date addYears(int $value)
 * @method static \Core\Support\Date addYear()
 * @method static \Core\Support\Date addMonths(int $value)
 * @method static \Core\Support\Date addMonth()
 * @method static \Core\Support\Date addDays(int $value)
 * @method static \Core\Support\Date addDay()
 * @method static \Core\Support\Date addWeeks(int $value)
 * @method static \Core\Support\Date addWeek()
 * @method static \Core\Support\Date addQuarters(int $value)
 * @method static \Core\Support\Date addQuarter()
 * @method static \Core\Support\Date addHours(int $value)
 * @method static \Core\Support\Date addHour()
 * @method static \Core\Support\Date addMinutes(int $value)
 * @method static \Core\Support\Date addMinute()
 * @method static \Core\Support\Date addSeconds(int $value)
 * @method static \Core\Support\Date addSecond()
 * @method static \Core\Support\Date subYears(int $value)
 * @method static \Core\Support\Date subYear()
 * @method static \Core\Support\Date subMonths(int $value)
 * @method static \Core\Support\Date subMonth()
 * @method static \Core\Support\Date subDays(int $value)
 * @method static \Core\Support\Date subDay()
 * @method static \Core\Support\Date subWeeks(int $value)
 * @method static \Core\Support\Date subWeek()
 * @method static \Core\Support\Date subQuarters(int $value)
 * @method static \Core\Support\Date subQuarter()
 * @method static \Core\Support\Date subHours(int $value)
 * @method static \Core\Support\Date subHour()
 * @method static \Core\Support\Date subMinutes(int $value)
 * @method static \Core\Support\Date subMinute()
 * @method static \Core\Support\Date subSeconds(int $value)
 * @method static \Core\Support\Date subSecond()
 * @method static string format(string $format)
 * @method static DateTime toDateTime()
 * @method static string toDateTimeString()
 * @method static string toDateString()
 * @method static string toTimeString()
 * @method static string toFormattedDateString()
 * @method static string toFormattedDateTimeString()
 * @method static string diffForHumans($other = null, bool $absolute = false)
 * @method static int timestamp()
 */
class Date extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        // return \Core\Support\Date::class;
        return 'date';
    }
}
