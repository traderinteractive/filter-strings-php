<?php

namespace TraderInteractive\Filter;

use TraderInteractive\Exceptions\FilterException;

/**
 * A collection of filters for JSON
 */
final class Json
{
    /**
     * @var bool
     */
    const DEFAULT_SHOULD_ALLOW_NULL = false;

    /**
     * @var int
     */
    const DEFAULT_RECURSION_DEPTH = 512;

    /**
     * @var string
     */
    const ERROR_CANNOT_BE_NULL = "Value cannot be null";

    /**
     * @var string
     */
    const ERROR_NOT_A_STRING = "Value '%s' is not a string";

    /**
     * @var string
     */
    const ERROR_INVALID_JSON = "JSON failed validation with message '%s'";

    /**
     * Parses a JSON string and returns the result.
     *
     * @param mixed $value           The value to filter.
     * @param bool  $shouldAllowNull Allows null values to pass through the filter when set to true.
     * @param int   $depth           The maximum recursion depth.
     *
     * @return array|bool|int|float|double|null
     *
     * @throws FilterException Thrown if the value is invalid.
     */
    public static function parse(
        $value,
        bool $shouldAllowNull = self::DEFAULT_SHOULD_ALLOW_NULL,
        int $depth = self::DEFAULT_RECURSION_DEPTH
    ) {
        return self::decode($value, $shouldAllowNull, true, $depth);
    }

    /**
     * Checks that the JSON is valid and returns the original value.
     *
     * @param mixed $value           The value to filter.
     * @param bool  $shouldAllowNull Allows null values to pass through the filter when set to true.
     * @param int   $depth           The maximum recursion depth.
     *
     * @return string|null
     *
     * @throws FilterException Thrown if the value is invalid.
     */
    public static function validate(
        $value,
        bool $shouldAllowNull = self::DEFAULT_SHOULD_ALLOW_NULL,
        int $depth = self::DEFAULT_RECURSION_DEPTH
    ) {
        self::decode($value, $shouldAllowNull, false, $depth);
        return $value;
    }

    /**
     * Parses a JSON string and returns the result.
     *
     * @param mixed $value               The value to filter.
     * @param bool  $shouldAllowNull     Allows null values to pass through the filter when set to true.
     * @param bool  $shouldDecodeToArray Decodes the JSON string to an associative array when set to true.
     * @param int   $depth               The maximum recursion depth.
     *
     * @return string|array|bool|int|float|double|null
     *
     * @throws FilterException Thrown if the value is invalid.
     */
    private static function decode($value, bool $shouldAllowNull, bool $shouldDecodeToArray, int $depth)
    {
        if ($shouldAllowNull && $value === null) {
            return $value;
        }

        self::ensureValueIsString($value);

        $value = json_decode($value, $shouldDecodeToArray, $depth);
        $lastErrorCode = json_last_error();
        if ($lastErrorCode !== JSON_ERROR_NONE) {
            $message = sprintf(self::ERROR_INVALID_JSON, json_last_error_msg());
            throw new FilterException($message, $lastErrorCode);
        }

        return $value;
    }

    /**
     * Ensures that the value is a string.
     *
     * @param mixed $value The value to filter.
     *
     * @throws FilterException Thrown if the value is not a string.
     */
    private static function ensureValueIsString($value)
    {
        if ($value === null) {
            throw new FilterException(self::ERROR_CANNOT_BE_NULL);
        }

        if (!is_string($value)) {
            throw new FilterException(sprintf(self::ERROR_NOT_A_STRING, var_export($value, true)));
        }
    }
}
