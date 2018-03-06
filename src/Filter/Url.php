<?php

namespace TraderInteractive\Filter;

use TraderInteractive\Exceptions\FilterException;

/**
 * A collection of filters for urls.
 */
final class Url
{
    /**
     * Filter an url
     *
     * Filters value as URL (according to » http://www.faqs.org/rfcs/rfc2396)
     *
     * The return value is the url, as expected by the \TraderInteractive\Filterer class.
     * By default, nulls are not allowed.
     *
     * @param mixed $value The value to filter.
     * @param bool $allowNull True to allow nulls through, and false (default) if nulls should not be allowed.
     *
     * @return string|null The passed in $value.
     *
     * @throws FilterException if the value did not pass validation.
     */
    public static function filter($value = null, bool $allowNull = false)
    {
        if (self::valueIsNullAndValid($allowNull, $value)) {
            return null;
        }

        self::validateString($value);

        return self::filterUrl($value);
    }

    private static function filterUrl(string $value = null) : string
    {
        $filteredUrl = filter_var($value, FILTER_VALIDATE_URL);
        if ($filteredUrl === false) {
            throw new FilterException("Value '{$value}' is not a valid url");
        }

        return $filteredUrl;
    }

    private static function valueIsNullAndValid(bool $allowNull, $value = null) : bool
    {
        if ($allowNull === false && $value === null) {
            throw new FilterException('Value failed filtering, $allowNull is set to false');
        }

        return $allowNull === true && $value === null;
    }

    private static function validateString($value)
    {
        if (!is_string($value)) {
            throw new FilterException("Value '" . var_export($value, true) . "' is not a string");
        }
    }
}
