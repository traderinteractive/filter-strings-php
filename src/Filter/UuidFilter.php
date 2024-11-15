<?php

namespace TraderInteractive\Filter;

use InvalidArgumentException;
use TraderInteractive\Exceptions\FilterException;

final class UuidFilter
{
    /**
     * @var string
     */
    const FILTER_ALIAS = 'uuid';

    /**
     * @var string
     */
    const UUID_PATTERN_FORMAT = '^[0-9A-F]{8}-[0-9A-F]{4}-[%d][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$';

    /**
     * @var string
     */
    const FILTER_ERROR_FORMAT = "Value '%s' is not a valid UUID. Versions checked (%s)";

    /**
     * @var string
     */
    const NIL_NOT_ALLOWED_ERROR_FORMAT = "Value '%s' is nil uuid, but nil values are not allowed.";

    /**
     * @var string
     */
    const NULL_NOT_ALLOWED_ERROR = "Value is null, but null values are not allowed.";

    /**
     * @var string
     */
    const UNSUPPORTED_VERSION_ERROR_FORMAT = 'Filter does not support UUID v%d';

    /**
     * @var string
     */
    const NIL_UUID = '00000000-0000-0000-0000-000000000000';

    /**
     * @var array
     * @internal
     */
    const VALID_UUID_VERSIONS = [1,2,3,4,5,6,7];


    /**
     * Filters a given string values to a valid UUID
     *
     * @param string|null $value     The value to be filtered.
     * @param bool        $allowNull Flag to allow value to be null.
     * @param bool        $allowNil  Flag to allow value to be a NIL UUID.
     * @param array       $versions  List of specific UUID version to validate against.
     *
     * @return string|null
     *
     * @throws FilterException Thrown if value cannot be filtered as an UUID.
     */
    public static function filter(
        string $value = null,
        bool $allowNull = false,
        bool $allowNil = false,
        array $versions = self::VALID_UUID_VERSIONS
    ) {
        if (self::valueIsNullAndValid($allowNull, $value)) {
            return null;
        }

        if (self::valueIsNilAndValid($allowNil, $value)) {
            return self::NIL_UUID;
        }

        self::validateVersions($versions);
        foreach ($versions as $version) {
            $pattern = sprintf(self::UUID_PATTERN_FORMAT, $version);
            if (preg_match("/{$pattern}/i", $value)) {
                return $value;
            }
        }

        throw new FilterException(
            sprintf(
                self::FILTER_ERROR_FORMAT,
                $value,
                implode(', ', $versions)
            )
        );
    }

    private static function valueIsNullAndValid(bool $allowNull, string $value = null): bool
    {
        if ($allowNull === false && $value === null) {
            throw new FilterException(self::NULL_NOT_ALLOWED_ERROR);
        }

        return $allowNull === true && $value === null;
    }

    private static function valueIsNilAndValid(bool $allowNil, string $value = null): bool
    {
        if ($allowNil === false && $value === self::NIL_UUID) {
            throw new FilterException(sprintf(self::NIL_NOT_ALLOWED_ERROR_FORMAT, $value));
        }

        return $allowNil === true && $value === self::NIL_UUID;
    }

    private static function validateVersions(array $versions)
    {
        foreach ($versions as $version) {
            if (!in_array($version, self::VALID_UUID_VERSIONS)) {
                throw new InvalidArgumentException(sprintf(self::UNSUPPORTED_VERSION_ERROR_FORMAT, $version));
            }
        }
    }
}
