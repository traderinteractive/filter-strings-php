<?php

namespace TraderInteractive\Filter;

use TraderInteractive\Exceptions\FilterException;

/**
 * A collection of filters for strings.
 */
final class Strings
{
    /**
     * Filter a string.
     *
     * Verify that the passed in value  is a string.  By default, nulls are not allowed, and the length is restricted
     * between 1 and PHP_INT_MAX.  These parameters can be overwritten for custom behavior.
     *
     * The return value is the string, as expected by the \TraderInteractive\Filterer class.
     *
     * @param mixed $value The value to filter.
     * @param bool $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     * @param int $minLength Minimum length to allow for $value.
     * @param int $maxLength Maximum length to allow for $value.
     * @return string|null The passed in $value.
     *
     * @throws FilterException if the value did not pass validation.
     * @throws \InvalidArgumentException if one of the parameters was not correctly typed.
     */
    public static function filter(
        string $value = null,
        bool $allowNull = false,
        int $minLength = 1,
        int $maxLength = PHP_INT_MAX
    ) {
        self::validateMinimumLength($minLength);
        self::validateMaximumLength($maxLength);

        if (self::valueIsNullAndValid($allowNull, $value)) {
            return null;
        }

        self::validateStringLength($value, $minLength, $maxLength);

        return $value;
    }

    /**
     * Explodes a string into an array using the given delimiter.
     *
     * For example, given the string 'foo,bar,baz', this would return the array ['foo', 'bar', 'baz'].
     *
     * @param string $value The string to explode.
     * @param string $delimiter The non-empty delimiter to explode on.
     * @return array The exploded values.
     *
     * @throws \InvalidArgumentException if the delimiter does not pass validation.
     */
    public static function explode(string $value, string $delimiter = ',')
    {
        if (empty($delimiter)) {
            throw new \InvalidArgumentException(
                "Delimiter '" . var_export($delimiter, true) . "' is not a non-empty string"
            );
        }

        return explode($delimiter, $value);
    }

    private static function validateMinimumLength(int $minLength)
    {
        if ($minLength < 0) {
            throw new \InvalidArgumentException('$minLength was not a positive integer value');
        }
    }

    private static function validateMaximumLength(int $maxLength)
    {
        if ($maxLength < 0) {
            throw new \InvalidArgumentException('$maxLength was not a positive integer value');
        }
    }

    private static function validateStringLength(string $value = null, int $minLength, int $maxLength)
    {
        $valueLength = strlen($value);
        if ($valueLength < $minLength || $valueLength > $maxLength) {
            throw new FilterException(
                sprintf(
                    "Value '%s' with length '%d' is less than '%d' or greater than '%d'",
                    $value,
                    $valueLength,
                    $minLength,
                    $maxLength
                )
            );
        }
    }

    private static function valueIsNullAndValid(bool $allowNull, string $value = null) : bool
    {
        if ($allowNull === false && $value === null) {
            throw new FilterException('Value failed filtering, $allowNull is set to false');
        }

        return $allowNull === true && $value === null;
    }
}
