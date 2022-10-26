<?php

namespace Caiocesar173\Booking\Enum;

use Caiocesar173\Utils\Abstracts\EnumAbstract;

/**
 * Will search the Status by the name informed and then return the id on statuses table
*/
abstract class TimeUnitEnum extends EnumAbstract
{
    const MINUTE = 'minute';
    const HOUR = 'hour';
    const DAY = 'day';
    const MONTH = 'month';

    public static function lists() {
        return [
            self::MINUTE => 'minute',
            self::HOUR => 'hour',
            self::DAY => 'day',
            self::MONTH => 'month',
        ];
    }
}