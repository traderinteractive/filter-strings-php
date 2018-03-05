<?php

namespace TraderInteractive\Filter;

use TraderInteractive\Exceptions\FilterException;

/**
 * A collection of filters for emails.
 */
final class Email
{
    /**
     * Filter an email
     *
     * The return value is the email, as expected by the \TraderInteractive\Filterer class.
     *
     * @param mixed $value The value to filter.
     *
     * @return string The passed in $value.
     *
     * @throws FilterException if the value did not pass validation.
     */
    public static function filter($value) : string
    {
        if (!is_string($value)) {
            throw new FilterException("Value '" . var_export($value, true) . "' is not a string");
        }

        $filteredEmail = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($filteredEmail === false) {
            throw new FilterException("Value '{$value}' is not a valid email");
        }

        return $filteredEmail;
    }
}
