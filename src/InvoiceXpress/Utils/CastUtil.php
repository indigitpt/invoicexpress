<?php

namespace InvoiceXpress\Utils;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use InvoiceXpress\Constants;

/**
 * Class CastUtil
 *
 * This is a simple ripoff from Illuminate\Eloquente\Database\Concerns
 *
 * @package InvoiceXpress\Utils
 */
class CastUtil
{
    /**
     * @param $value
     * @param $type
     * @return array|bool|float|Carbon|int|mixed|string|null
     */
    public static function cast($value, $type)
    {
        if ($value === null) {
            return $value;
        }

        switch ($type) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'real':
            case 'float':
            case 'double':
                return self::fromFloat($value);
            case 'decimal':
                return self::asDecimal($value, explode(':', $value, 2)[1]);
            case 'string':
                return (string)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            case 'object':
                return self::fromJson($value, true);
            case 'array':
            case 'json':
                return self::fromJson($value);
            case 'to_json':
                return self::toJson($value);
            case 'date':
                return self::asDate($value);
            case 'datetime':
            case 'custom_datetime':
                return self::asDateTime($value);
            case 'timestamp':
                return self::asTimestamp($value);
            case 'to_date':
                return self::toDate($value);
            default:
                return $value;
        }
    }

    /**
     * Decode the given float.
     *
     * @param mixed $value
     * @return mixed
     */
    protected static function fromFloat($value)
    {
        switch ((string)$value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float)$value;
        }
    }

    /**
     * Return a decimal as string.
     *
     * @param float $value
     * @param int $decimals
     * @return string
     */
    protected static function asDecimal($value, $decimals)
    {
        return number_format($value, $decimals, '.', '');
    }

    /**
     * Decode the given JSON back into an array or object.
     *
     * @param string $value
     * @param bool $asObject
     * @return mixed
     */
    public static function fromJson($value, $asObject = false)
    {
        return json_decode($value, !$asObject);
    }

    /**
     * Encode the given JSON back into an array or object.
     *
     * @param array $value
     * @return mixed
     */
    public static function toJson($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        return $value;
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param mixed $value
     * @return Carbon
     */
    protected static function asDate($value)
    {
        return self::asDateTime($value)->startOfDay();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     * @return Carbon
     */
    protected static function asDateTime($value)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof Carbon || $value instanceof CarbonInterface) {
            return Date::instance($value);
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return Date::parse(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Date::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (self::isStandardDateFormat($value)) {
            return Date::instance(Carbon::createFromFormat('Y-m-d', $value)->startOfDay());
        }

        $format = Constants::DATE_FORMAT_CARBON;

        // https://bugs.php.net/bug.php?id=75577
        if (version_compare(PHP_VERSION, '7.3.0-dev', '<')) {
            $format = str_replace('.v', '.u', $format);
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        return Date::createFromFormat($format, $value);
    }

    /**
     * Determine if the given value is a standard date format.
     *
     * @param string $value
     * @return bool
     */
    protected static function isStandardDateFormat($value)
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param mixed $value
     * @return int
     */
    protected static function asTimestamp($value)
    {
        return self::asDateTime($value)->getTimestamp();
    }

    /**
     * Grabs the date format in whatever format it is, and tries to convert into InvoiceExpress Standard
     *
     * @param \Carbon\Carbon|string|null $value
     * @return null|string
     */
    protected static function toDate($value)
    {
        if (null === $value) {
            return $value;
        }

        # If we are reading object, we will convert it back to ensure the output
        if (is_string($value)) {
            return Carbon::createFromFormat(Constants::DATE_FORMAT_CARBON,
                $value)->format(Constants::DATE_FORMAT_CARBON);
        }

        return $value->format(Constants::DATE_FORMAT_CARBON);
    }

}
