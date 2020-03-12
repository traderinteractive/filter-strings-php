<?php


namespace TraderInteractive\Filter;

use Throwable;
use TraderInteractive\Exceptions\FilterException;

final class PhoneFilter
{

    /**
     * The pattern for the separations between numbers.
     *
     * @var string
     */
    const SEPARATOR_PATTERN = ' *[-.]? *';

    /**
     * The pattern for the area code.
     *
     * @var string
     */
    const AREA_CODE_PATTERN = '(?:\(([2–9]\d\d)\)|([2-9]\d\d))?';

    /**
     * The pattern for the exchange code. Also known as the central office code.
     *
     * @var string
     */
    const EXCHANGE_CODE_PATTERN = '([2-9]\d\d)';

    /**
     * The pattern for the station code. Also known as the line number or subscriber number.
     *
     * @var string
     */
    const STATION_CODE_PATTERN = '(\d{4})';

    /**
     * The pattern for phone numbers according to the North American Numbering Plan specification.
     *
     * @var string
     */
    const PHONE_PATTERN = (''
        . '/^ *'
        . self::AREA_CODE_PATTERN
        . self::SEPARATOR_PATTERN
        . self::EXCHANGE_CODE_PATTERN
        . self::SEPARATOR_PATTERN
        . self::STATION_CODE_PATTERN
        . ' *$/'
    );

    /**
     * @var string
     */
    const ERROR_INVALID_PHONE_NUMBER = "Value '%s' is not a valid phone number.";

    /**
     * @var string
     */
    const ERROR_VALUE_CANNOT_BE_NULL = 'Value cannot be null';

    /**
     * @var string
     */
    const DEFAULT_FILTERED_PHONE_FORMAT = '{area}{exchange}{station}';

    /**
     * @var bool
     */
    private $allowNull;

    /**
     * @var string
     */
    private $filteredPhoneFormat;

    /**
     * @param bool   $allowNull           Flag to allow value to be null
     * @param string $filteredPhoneFormat The format for which the filtered phone value will be returned.
     */
    public function __construct(
        bool $allowNull = false,
        string $filteredPhoneFormat = self::DEFAULT_FILTERED_PHONE_FORMAT
    ) {
        $this->allowNull = $allowNull;
        $this->filteredPhoneFormat = $filteredPhoneFormat;
    }

    /**
     * @param mixed  $value The value to filter.
     *
     * @return string|null
     *
     * @throws FilterException Thrown when the value does not pass filtering.
     */
    public function __invoke($value)
    {
        return self::filter($value, $this->allowNull, $this->filteredPhoneFormat);
    }

    /**
     * @param mixed  $value               The value to filter.
     * @param bool   $allowNull           Flag to allow value to be null
     * @param string $filteredPhoneFormat The format for which the filtered phone value will be returned.
     *
     * @return string|null
     *
     * @throws FilterException Thrown when the value does not pass filtering.
     */
    public static function filter(
        $value,
        bool $allowNull = false,
        string $filteredPhoneFormat = self::DEFAULT_FILTERED_PHONE_FORMAT
    ) {
        if ($value === null) {
            return self::returnNullValue($allowNull);
        }

        $value = self::getValueAsString($value);
        $matches = [];
        if (!preg_match(self::PHONE_PATTERN, $value, $matches)) {
            $message = sprintf(self::ERROR_INVALID_PHONE_NUMBER, $value);
            throw new FilterException($message);
        }

        list($phone, $areaWithParenthesis, $area, $exchange, $station) = $matches;
        if ($areaWithParenthesis !== '') {
            $area = $areaWithParenthesis;
        }

        $search = ['{area}', '{exchange}', '{station}'];
        $replace = [$area, $exchange, $station];
        return str_replace($search, $replace, $filteredPhoneFormat);
    }

    private static function returnNullValue(bool $allowNull)
    {
        if ($allowNull === false) {
            throw new FilterException(self::ERROR_VALUE_CANNOT_BE_NULL);
        }

        return null;
    }

    private static function getValueAsString($value) : string
    {
        if (is_scalar($value)) {
            return (string)$value;
        }

        $message = sprintf(self::ERROR_INVALID_PHONE_NUMBER, var_export($value, true));
        throw new FilterException($message);
    }
}
