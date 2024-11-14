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
     * @var array
     * @internal
     */
    const VALID_UUID_VERSIONS = [1,2,3,4,5,6,7];


    /**
     * Filters a given string values to a valid UUID
     *
     * @param string|null $value     The value to be filtered.
     * @param bool        $allowNull Flag to allow value to be null.
     * @param array       $versions  List of specific UUID version to validate against.
     *
     * @return string|null
     *
     * @throws FilterException Thrown if value cannot be filtered as an UUID.
     */
    public static function filter(
        string $value = null,
        bool $allowNull = false,
        array $versions = self::VALID_UUID_VERSIONS
    ) {
        if (self::valueIsNullAndValid($allowNull, $value)) {
            return null;
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
            throw new FilterException('Value failed filtering, $allowNull is set to false');
        }

        return $allowNull === true && $value === null;
    }

    private static function validateVersions(array $versions)
    {
        foreach ($versions as $version) {
            if (!in_array($version, self::VALID_UUID_VERSIONS)) {
                throw new InvalidArgumentException("Filter does not support UUID v{$version}");
            }
        }
    }
}
