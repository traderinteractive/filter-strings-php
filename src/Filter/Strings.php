<?php

namespace TraderInteractive\Filter;

use InvalidArgumentException;
use TraderInteractive\Exceptions\FilterException;
use TypeError;

/**
 * A collection of filters for strings.
 */
final class Strings
{
    /**
     * @var string
     */
    const EXPLODE_PAD_LEFT = 'left';

    /**
     * @var string
     */
    const EXPLODE_PAD_RIGHT = 'right';

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
        $value = null,
        bool $allowNull = false,
        int $minLength = 1,
        int $maxLength = PHP_INT_MAX
    ) {
        self::validateMinimumLength($minLength);
        self::validateMaximumLength($maxLength);

        if (self::valueIsNullAndValid($allowNull, $value)) {
            return null;
        }

        $value = self::enforceValueCanBeCastAsString($value);

        self::validateStringLength($value, $minLength, $maxLength);

        return $value;
    }

    /**
     * Explodes a string into an array using the given delimiter.
     *
     * For example, given the string 'foo,bar,baz', this would return the array ['foo', 'bar', 'baz'].
     *
     * @param string     $value     The string to explode.
     * @param string     $delimiter The non-empty delimiter to explode on.
     * @param int|null   $padLength The number of elements to be returned in the result.
     * @param mixed      $padValue  The value to use when padding the resulting array.
     * @param string     $padType   Argument to specify if the resulting array should be padded on the left or right.
     *
     * @return array The exploded values.
     *
     * @throws \InvalidArgumentException if the delimiter does not pass validation.
     */
    public static function explode(
        $value,
        string $delimiter = ',',
        int $padLength = null,
        $padValue = null,
        string $padType = self::EXPLODE_PAD_RIGHT
    ) : array {
        self::validateIfObjectIsAString($value);

        if (empty($delimiter)) {
            throw new \InvalidArgumentException(
                "Delimiter '" . var_export($delimiter, true) . "' is not a non-empty string"
            );
        }

        $values = explode($delimiter, $value);
        $padLength = $padLength ?? count($values);
        while (count($values) < $padLength) {
            if ($padType === self::EXPLODE_PAD_RIGHT) {
                array_push($values, $padValue);
                continue;
            }

            if ($padType === self::EXPLODE_PAD_LEFT) {
                array_unshift($values, $padValue);
                continue;
            }

            throw new InvalidArgumentException('Invalid $padType value provided');
        }

        return $values;
    }

    /**
     * This filter takes the given string and translates it using the given value map.
     *
     * @param string $value    The string value to translate
     * @param array  $valueMap Array of key value pairs where a key will match the given $value.
     *
     * @return string
     */
    public static function translate(string $value, array $valueMap) : string
    {
        if (!array_key_exists($value, $valueMap)) {
            throw new FilterException("The value '{$value}' was not found in the translation map array.");
        }

        return $valueMap[$value];
    }

    /**
     * This filter prepends $prefix and appends $suffix to the string value.
     *
     * @param mixed  $value  The string value to which $prefix and $suffix will be added.
     * @param string $prefix The value to prepend to the string.
     * @param string $suffix The value to append to the string.
     *
     * @return string
     *
     * @throws FilterException Thrown if $value cannot be casted to a string.
     */
    public static function concat($value, string $prefix = '', string $suffix = '') : string
    {
        self::enforceValueCanBeCastAsString($value);
        return "{$prefix}{$value}{$suffix}";
    }

    /**
     * This filter trims and removes superfluous whitespace characters from the given string.
     *
     * @param string|null $value                     The string to compress.
     * @param bool        $replaceVerticalWhitespace Flag to replace vertical whitespace such as newlines with
     *                                               single space.
     *
     * @return string|null
     */
    public static function compress(string $value = null, bool $replaceVerticalWhitespace = false)
    {
        if ($value === null) {
            return null;
        }

        $pattern = $replaceVerticalWhitespace ? '\s+' : '\h+';

        return trim(preg_replace("/{$pattern}/", ' ', $value));
    }

    /**
     * This filter replaces the given words with a replacement character.
     *
     * @param mixed          $value       The raw input to run the filter against.
     * @param array|callable $words       The words to filter out.
     * @param string         $replacement The character to replace the words with.
     *
     * @return string|null
     *
     * @throws FilterException Thrown when a bad value is encountered.
     */
    public static function redact(
        $value,
        $words,
        string $replacement = ''
    ) {
        if ($value === null || $value === '') {
            return $value;
        }

        $stringValue = self::filter($value);
        if (is_callable($words)) {
            $words = $words();
        }

        if (is_array($words) === false) {
            throw new FilterException("Words was not an array or a callable that returns an array");
        }

        return self::replaceWordsWithReplacementString($stringValue, $words, $replacement);
    }

    /**
     * Strip HTML and PHP tags from a string and, optionally, replace the tags with a string.
     * Unlike the strip_tags function, this method will return null if a null value is given.
     * The native php function will return an empty string.
     *
     * @param string|null $value       The input string.
     * @param string      $replacement The string to replace the tags with. Defaults to an empty string.
     *
     * @return string|null
     */
    public static function stripTags(string $value = null, string $replacement = '')
    {
        if ($value === null) {
            return null;
        }

        if ($replacement === '') {
            return strip_tags($value);
        }

        $findTagEntities = '/<[^>]+?>/';
        $valueWithReplacements = preg_replace($findTagEntities, $replacement, $value);
        return strip_tags($valueWithReplacements); // use built-in as a safeguard to ensure tags are stripped
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
            $format = "Value '%s' with length '%d' is less than '%d' or greater than '%d'";
            throw new FilterException(
                sprintf($format, $value, $valueLength, $minLength, $maxLength)
            );
        }
    }

    private static function valueIsNullAndValid(bool $allowNull, $value = null) : bool
    {
        if ($allowNull === false && $value === null) {
            throw new FilterException('Value failed filtering, $allowNull is set to false');
        }

        return $allowNull === true && $value === null;
    }

    private static function validateIfObjectIsAString($value)
    {
        if (!is_string($value)) {
            throw new FilterException("Value '" . var_export($value, true) . "' is not a string");
        }
    }

    private static function enforceValueCanBeCastAsString($value)
    {
        try {
            $value = (
                function (string $str) : string {
                    return $str;
                }
            )($value);
        } catch (TypeError $te) {
            throw new FilterException(sprintf("Value '%s' is not a string", var_export($value, true)));
        }

        return $value;
    }

    private static function replaceWordsWithReplacementString(string $value, array $words, string $replacement) : string
    {
        $matchingWords = self::getMatchingWords($words, $value);
        if (count($matchingWords) === 0) {
            return $value;
        }

        $replacements = self::generateReplacementsMap($matchingWords, $replacement);

        return str_ireplace($matchingWords, $replacements, $value);
    }

    private static function getMatchingWords(array $words, string $value) : array
    {
        $matchingWords = [];
        foreach ($words as $word) {
            $escapedWord = preg_quote($word, '/');
            $caseInsensitiveWordPattern = "/\b{$escapedWord}\b/i";
            if (preg_match($caseInsensitiveWordPattern, $value)) {
                $matchingWords[] = $word;
            }
        }

        return $matchingWords;
    }

    private static function generateReplacementsMap(array $words, string $replacement) : array
    {
        $replacement = mb_substr($replacement, 0, 1);

        return array_map(
            function ($word) use ($replacement) {
                if ($replacement === '') {
                    return '';
                }

                return str_repeat($replacement, strlen($word));
            },
            $words
        );
    }
}
