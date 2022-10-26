<?php

namespace Caiocesar173\Booking\Enum;

use Caiocesar173\Utils\Abstracts\EnumAbstract;

/**
 * Will search the Status by the name informed and then return the id on statuses table
*/
abstract class DatesRangeEnum extends EnumAbstract
{
    const DATETIMES = 'datetimes';
    const DATES = 'dates';
    const MONTHS = 'months';
    const WEEKS = 'weeks';
    const DAYS = 'days';
    const TIMES = 'times';
    const SUNDAY = 'sunday';
    const MONDAY = 'monday';
    const TUESDAY = 'tuesday';
    const WEDNESDAY = 'wednesday';
    const THURSDAY = 'thursday';
    const FRIDAY = 'friday';
    const SATURDAY = 'saturday';

    public static function lists() {
        return [
            self::DATETIMES => 'datetimes',
            self::DATES => 'dates',
            self::MONTHS => 'months',
            self::WEEKS => 'weeks',
            self::DAYS => 'days',
            self::TIMES => 'times',
            self::SUNDAY => 'sunday',
            self::MONDAY => 'monday',
            self::TUESDAY => 'tuesday',
            self::WEDNESDAY => 'wednesday',
            self::THURSDAY => 'thursday',
            self::FRIDAY => 'friday',
            self::SATURDAY => 'saturday',
        ];
    }
}