<?php

namespace Core\Support;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;

/**
 * A Carbon-like date and time manipulation class.
 */
class Date
{
    protected DateTime $dateTime;

    /**
     * Create a new Date instance.
     *
     * @param string|DateTime|null $time
     * @param string|DateTimeZone|null $timezone
     */
    public function __construct($time = null, $timezone = null)
    {
        if ($time instanceof DateTime) {
            $this->dateTime = clone $time;
        } else {
            $this->dateTime = new DateTime($time ?? 'now');
        }

        if ($timezone) {
            $this->setTimezone($timezone);
        }
    }

    /**
     * Create a new Date instance for the current time.
     *
     * @return static
     */
    public static function now(): static
    {
        return new static();
    }

    /**
     * Create a Date instance from a specific format.
     *
     * @param string $format
     * @param string $time
     * @param string|DateTimeZone|null $timezone
     * @return static
     */
    public static function createFromFormat(string $format, string $time, $timezone = null): static
    {
        $dateTime = DateTime::createFromFormat($format, $time);
        if ($dateTime === false) {
            throw new InvalidArgumentException("Invalid date format: {$format}");
        }
        return new static($dateTime, $timezone);
    }

    /**
     * Set the timezone.
     *
     * @param string|DateTimeZone $timezone
     * @return static
     */
    public function setTimezone($timezone): static
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }
        $this->dateTime->setTimezone($timezone);
        return $this;
    }

    /**
     * Get the timezone.
     *
     * @return DateTimeZone
     */
    public function getTimezone(): DateTimeZone
    {
        return $this->dateTime->getTimezone();
    }

    /**
     * Add years to the date.
     *
     * @param int $value
     * @return static
     */
    public function addYears(int $value): static
    {
        $this->dateTime->modify("+$value years");
        return $this;
    }

    /**
     * Add one year to the date.
     *
     * @return static
     */
    public function addYear(): static
    {
        return $this->addYears(1);
    }

    /**
     * Add months to the date.
     *
     * @param int $value
     * @return static
     */
    public function addMonths(int $value): static
    {
        $this->dateTime->modify("+$value months");
        return $this;
    }

    /**
     * Add one month to the date.
     *
     * @return static
     */
    public function addMonth(): static
    {
        return $this->addMonths(1);
    }

    /**
     * Add days to the date.
     *
     * @param int $value
     * @return static
     */
    public function addDays(int $value): static
    {
        $this->dateTime->modify("+$value days");
        return $this;
    }

    /**
     * Add one day to the date.
     *
     * @return static
     */
    public function addDay(): static
    {
        return $this->addDays(1);
    }

    /**
     * Add weeks to the date.
     *
     * @param int $value
     * @return static
     */
    public function addWeeks(int $value): static
    {
        $this->dateTime->modify("+$value weeks");
        return $this;
    }

    /**
     * Add one week to the date.
     *
     * @return static
     */
    public function addWeek(): static
    {
        return $this->addWeeks(1);
    }

    /**
     * Add quarters to the date.
     *
     * @param int $value
     * @return static
     */
    public function addQuarters(int $value): static
    {
        return $this->addMonths($value * 3);
    }

    /**
     * Add one quarter to the date.
     *
     * @return static
     */
    public function addQuarter(): static
    {
        return $this->addQuarters(1);
    }

    /**
     * Add hours to the date.
     *
     * @param int $value
     * @return static
     */
    public function addHours(int $value): static
    {
        $this->dateTime->modify("+$value hours");
        return $this;
    }

    /**
     * Add one hour to the date.
     *
     * @return static
     */
    public function addHour(): static
    {
        return $this->addHours(1);
    }

    /**
     * Add minutes to the date.
     *
     * @param int $value
     * @return static
     */
    public function addMinutes(int $value): static
    {
        $this->dateTime->modify("+$value minutes");
        return $this;
    }

    /**
     * Add one minute to the date.
     *
     * @return static
     */
    public function addMinute(): static
    {
        return $this->addMinutes(1);
    }

    /**
     * Add seconds to the date.
     *
     * @param int $value
     * @return static
     */
    public function addSeconds(int $value): static
    {
        $this->dateTime->modify("+$value seconds");
        return $this;
    }

    /**
     * Add one second to the date.
     *
     * @return static
     */
    public function addSecond(): static
    {
        return $this->addSeconds(1);
    }

    /**
     * Subtract years from the date.
     *
     * @param int $value
     * @return static
     */
    public function subYears(int $value): static
    {
        $this->dateTime->modify("-$value years");
        return $this;
    }

    /**
     * Subtract one year from the date.
     *
     * @return static
     */
    public function subYear(): static
    {
        return $this->subYears(1);
    }

    /**
     * Subtract months from the date.
     *
     * @param int $value
     * @return static
     */
    public function subMonths(int $value): static
    {
        $this->dateTime->modify("-$value months");
        return $this;
    }

    /**
     * Subtract one month from the date.
     *
     * @return static
     */
    public function subMonth(): static
    {
        return $this->subMonths(1);
    }

    /**
     * Subtract days from the date.
     *
     * @param int $value
     * @return static
     */
    public function subDays(int $value): static
    {
        $this->dateTime->modify("-$value days");
        return $this;
    }

    /**
     * Subtract one day from the date.
     *
     * @return static
     */
    public function subDay(): static
    {
        return $this->subDays(1);
    }

    /**
     * Subtract weeks from the date.
     *
     * @param int $value
     * @return static
     */
    public function subWeeks(int $value): static
    {
        $this->dateTime->modify("-$value weeks");
        return $this;
    }

    /**
     * Subtract one week from the date.
     *
     * @return static
     */
    public function subWeek(): static
    {
        return $this->subWeeks(1);
    }

    /**
     * Subtract quarters from the date.
     *
     * @param int $value
     * @return static
     */
    public function subQuarters(int $value): static
    {
        return $this->subMonths($value * 3);
    }

    /**
     * Subtract one quarter from the date.
     *
     * @return static
     */
    public function subQuarter(): static
    {
        return $this->subQuarters(1);
    }

    /**
     * Subtract hours from the date.
     *
     * @param int $value
     * @return static
     */
    public function subHours(int $value): static
    {
        $this->dateTime->modify("-$value hours");
        return $this;
    }

    /**
     * Subtract one hour from the date.
     *
     * @return static
     */
    public function subHour(): static
    {
        return $this->subHours(1);
    }

    /**
     * Subtract minutes from the date.
     *
     * @param int $value
     * @return static
     */
    public function subMinutes(int $value): static
    {
        $this->dateTime->modify("-$value minutes");
        return $this;
    }

    /**
     * Subtract one minute from the date.
     *
     * @return static
     */
    public function subMinute(): static
    {
        return $this->subMinutes(1);
    }

    /**
     * Subtract seconds from the date.
     *
     * @param int $value
     * @return static
     */
    public function subSeconds(int $value): static
    {
        $this->dateTime->modify("-$value seconds");
        return $this;
    }

    /**
     * Subtract one second from the date.
     *
     * @return static
     */
    public function subSecond(): static
    {
        return $this->subSeconds(1);
    }

    /**
     * Format the date.
     *
     * @param string $format
     * @return string
     */
    public function format(string $format): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * Get the date as a DateTime object.
     *
     * @return DateTime
     */
    public function toDateTime(): DateTime
    {
        return clone $this->dateTime;
    }

    /**
     * Format as a full date and time string (e.g., 2025-05-03 14:30:00).
     *
     * @return string
     */
    public function toDateTimeString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * Format as a date string (e.g., 2025-05-03).
     *
     * @return string
     */
    public function toDateString(): string
    {
        return $this->format('Y-m-d');
    }

    /**
     * Format as a time string (e.g., 14:30:00).
     *
     * @return string
     */
    public function toTimeString(): string
    {
        return $this->format('H:i:s');
    }

    /**
     * Format as a human-readable date (e.g., May 3, 2025).
     *
     * @return string
     */
    public function toFormattedDateString(): string
    {
        return $this->format('F j, Y');
    }

    /**
     * Format as a human-readable date and time (e.g., May 3, 2025, 2:30 PM).
     *
     * @return string
     */
    public function toFormattedDateTimeString(): string
    {
        return $this->format('F j, Y, g:i A');
    }

    /**
     * Get the difference in human-readable format (e.g., "5 seconds ago").
     *
     * @param Date|DateTime|string|null $other
     * @param bool $absolute
     * @return string
     */
    public function diffForHumans($other = null, bool $absolute = false): string
    {
        $other = $other instanceof self ? $other->toDateTime() : new DateTime($other ?? 'now');
        $interval = $this->dateTime->diff($other);

        $units = [
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        foreach ($units as $key => $unit) {
            if ($interval->$key > 0) {
                $value = $interval->$key;
                $suffix = $value === 1 ? $unit : "{$unit}s";
                $prefix = $absolute ? '' : ($interval->invert ? ' ago' : ' from now');
                return "{$value} {$suffix}{$prefix}";
            }
        }

        return $absolute ? '0 seconds' : 'just now';
    }

    /**
     * Get the timestamp.
     *
     * @return int
     */
    public function timestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * Convert to string (Y-m-d H:i:s).
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toDateTimeString();
    }
}
