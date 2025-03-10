<?php

namespace TraderInteractive\Filter;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\FilterException;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Strings
 * @covers ::<private>
 */
final class StringsTest extends TestCase
{
    /**
     * Verify basic use of filter
     *
     * @test
     * @covers ::filter
     * @dataProvider filterData
     *
     * @param mixed $input    The input.
     * @param mixed $expected The expected value(s).
     *
     * @return void
     * @throws FilterException
     */
    public function filter($input, $expected)
    {
        $this->assertSame($expected, Strings::filter($input));
    }

    /**
     * Data provider for basic filter tests
     *
     * @return array
     */
    public function filterData()
    {
        return [
            'string' => ['abc', 'abc'],
            'int' => [1, '1'],
            'float' => [1.1, '1.1'],
            'bool' => [true, '1'],
            'object' => [new \SplFileInfo(__FILE__), __FILE__],
        ];
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullPass()
    {
        $this->assertNull(Strings::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullFail()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage('Value failed filtering, $allowNull is set to false');
        Strings::filter(null);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinLengthPass()
    {
        $this->assertSame('a', Strings::filter('a'));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinLengthFail()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        Strings::filter('');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMaxLengthPass()
    {
        $this->assertSame('a', Strings::filter('a', false, 0, 1));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMaxLengthFail()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage("Value 'a' with length '1' is less than '0' or greater than '0'");
        Strings::filter('a', false, 0, 0);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinLengthNotInteger()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$minLength was not a positive integer value');
        Strings::filter('a', false, -1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMaxLengthNotInteger()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$maxLength was not a positive integer value');
        Strings::filter('a', false, 1, -1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMinLengthNegative()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$minLength was not a positive integer value');
        Strings::filter('a', false, -1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterMaxLengthNegative()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$maxLength was not a positive integer value');
        Strings::filter('a', false, 1, -1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterWithScalar()
    {
        $this->assertSame('24141', Strings::filter(24141));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterWithObject()
    {
        $testObject = new class() {
            private $data;

            public function __construct()
            {
                $this->data = [1,2,3,4,5];
            }

            public function __toString()
            {
                return implode(',', $this->data);
            }
        };

        $this->assertSame('1,2,3,4,5', Strings::filter(new $testObject));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterWithObjectNoToStringMethod()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessageMatches("/Value '\\\\?class\@anonymous/");
        $testObject = new class() {
            private $data;

            public function __construct()
            {
                $this->data = [1, 2, 3, 4, 5];
            }
        };

        Strings::filter(new $testObject);
    }

    /**
     * @test
     * @covers ::translate
     */
    public function translateValue()
    {
        $map = ['foo' => '100', 'bar' => '200'];
        $this->assertSame('100', Strings::translate('foo', $map));
    }

    /**
     * @test
     * @covers ::translate
     */
    public function translateValueNotFoundInMap()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage("The value 'baz' was not found in the translation map array.");
        $map = ['foo' => '100', 'bar' => '200'];
        Strings::translate('baz', $map);
    }

    /**
     * Verifies basic explode functionality.
     *
     * @test
     * @covers ::explode
     */
    public function explode()
    {
        $this->assertSame(['a', 'bcd', 'e'], Strings::explode('a,bcd,e'));
    }

    /**
     * Verifies explode with a custom delimiter.
     *
     * @test
     * @covers ::explode
     */
    public function explodeCustomDelimiter()
    {
        $this->assertSame(['a', 'b', 'c', 'd,e'], Strings::explode('a b c d,e', ' '));
    }

    /**
     * @test
     * @covers ::explode
     */
    public function explodeNonString()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage("Value '1234' is not a string");
        Strings::explode(1234, '');
    }

    /**
     * Verifies explode filter with an empty delimiter.
     *
     * @test
     * @covers ::explode
     */
    public function explodeEmptyDelimiter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Delimiter '''' is not a non-empty string");
        Strings::explode('test', '');
    }

    /**
     * @test
     * @covers ::stripTags
     * @dataProvider provideStripTags
     *
     * @param string|null $value
     * @param string      $replacement
     * @param string|null $expected
     */
    public function stripTags($value, string $replacement, $expected)
    {
        $actual = Strings::stripTags($value, $replacement);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function provideStripTags()
    {
        return [
            'null returns null' => [
                'value' => null,
                'replacement' => '',
                'expected' => null,
            ],
            'remove html from string' => [
                'value' => 'A string with <p>paragraph</p> tags',
                'replacement' => '',
                'expected' => 'A string with paragraph tags',
            ],
            'remove xml and replace with space' => [
                'value' => '<something>inner value</something>',
                'replacement' => ' ',
                'expected' => ' inner value ',
            ],
            'remove multiline html from string' => [
                'value' => "<p\nclass='something'\nstyle='display:none'></p>",
                'replacement' => ' ',
                'expected' => '  ',
            ],
            'remove php tags' => [
                'value' => '<?php some php code',
                'replacement' => ' ',
                'expected' => '',
            ],
            'remove shorthand php tags' => [
                'value' => '<?= some php code ?> something else',
                'replacement' => ' ',
                'expected' => '  something else',
            ],
            'do not remove unmatched <' => [
                'value' => '1 < 3',
                'replacement' => ' ',
                'expected' => '1 < 3',
            ],
            'do not remove unmatched >' => [
                'value' => '3 > 1',
                'replacement' => ' ',
                'expected' => '3 > 1',
            ],
        ];
    }

    /**
     * @test
     * @covers ::concat
     */
    public function concat()
    {
        $this->assertSame('prefixstringsuffix', Strings::concat('string', 'prefix', 'suffix'));
    }

    /**
     * Verify behavior of concat() when $value is not filterable
     *
     * @test
     * @covers ::concat
     *
     * @return void
     */
    public function concatValueNotFilterable()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        Strings::concat(new \StdClass(), 'prefix', 'suffix');
    }

    /**
     * @test
     * @covers ::concat
     */
    public function concatScalarValue()
    {
        $this->assertSame('prefix123suffix', Strings::concat(123, 'prefix', 'suffix'));
    }

    /**
     * @test
     * @covers ::concat
     */
    public function concatObjectValue()
    {
        $this->assertSame(
            'prefix' . __FILE__ . 'suffix',
            Strings::concat(new \SplFileInfo(__FILE__), 'prefix', 'suffix')
        );
    }

    /**
     * @test
     * @covers ::compress
     */
    public function compressRemovesSuperfluousWhitespace()
    {
        $this->assertSame('a compressed string', Strings::compress('  a   compressed     string    '));
    }

    /**
     * @test
     * @covers ::compress
     */
    public function compressReturnsNullIfValueIsNull()
    {
        $this->assertNull(Strings::compress(null));
    }

    /**
     * @test
     * @covers ::compress
     */
    public function compressRemovesNewLines()
    {
        $input = " This string\nhas     superfluous   whitespace  and  \nnewlines\n";
        $this->assertSame(
            'This string has superfluous whitespace and newlines',
            Strings::compress($input, true)
        );
    }

    /**
     * @test
     * @covers ::compress
     */
    public function compressIgnoresNewLinesByDefault()
    {
        $input = " This string\nhas     superfluous   whitespace  and  \nnewlines\n";
        $this->assertSame(
            "This string\nhas superfluous whitespace and \nnewlines",
            Strings::compress($input)
        );
    }

    /**
     * @test
     * @covers ::redact
     * @dataProvider provideRedact
     *
     * @param string|null    $value       The value to pass to the filter.
     * @param array|callable $words       The words to pass to the filter.
     * @param string         $replacement The replacement to pass to the filter.
     * @param string|null    $expected    The expected result.
     */
    public function redact($value, $words, string $replacement, $expected)
    {
        $actual = Strings::redact($value, $words, $replacement);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function provideRedact() : array
    {
        return [
            'null value' => [
                'value' => null,
                'words' => [],
                'replacement' => '',
                'expected' => null,
            ],
            'empty string' => [
                'value' => '',
                'words' => [],
                'replacement' => '',
                'expected' => '',
            ],
            'replace with empty' => [
                'value' => 'this message contains something that you want removed',
                'words' => ['something that you want removed'],
                'replacement' => '',
                'expected' => 'this message contains ',
            ],
            'replace with *' => [
                'value' => 'replace certain words that you might want to remove',
                'words' => ['might', 'certain'],
                'replacement' => '*',
                'expected' => 'replace ******* words that you ***** want to remove',
            ],
            'replace with █' => [
                'value' => 'redact specific dates and secret locations',
                'words' => ['secret locations', 'specific dates'],
                'replacement' => '█',
                'expected' => 'redact ██████████████ and ████████████████',
            ],
            'replace with multi-character string uses first character' => [
                'value' => 'replace some particular words',
                'words' => ['particular', 'words', 'some'],
                'replacement' => ' *** ',
                'expected' => 'replace                      ',
            ],
            'no replacements' => [
                'value' => 'some perfectly normal string',
                'words' => ['undesired', 'words'],
                'replacement' => '*',
                'expected' => 'some perfectly normal string',
            ],
            'closure provides words' => [
                'value' => 'doe a deer, a female deer',
                'words' => function () {
                    return ['doe', 'deer'];
                },
                'replacement' => '-',
                'expected' => '--- a ----, a female ----',
            ],
        ];
    }

    /**
     * @test
     * @covers ::redact
     * @dataProvider provideRedactFailsOnBadInput
     *
     * @param mixed  $value       The value to pass to the filter.
     * @param mixed  $words       The words to pass to the filter.
     * @param string $replacement The replacement to pass to the filter.
     * @param string $exception   The exception to expect.
     * @param string $message     The exception message to expect.
     */
    public function redactFailsOnBadInput($value, $words, string $replacement, string $exception, string $message)
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        Strings::redact($value, $words, $replacement);
    }

    /**
     * @return array
     */
    public function provideRedactFailsOnBadInput() : array
    {
        return [
            'non-string value' => [
                'value' => ['bad', 'input'],
                'words' => [],
                'replacement' => '',
                'exception' => FilterException::class,
                'message' => "Value '" . var_export(['bad', 'input'], true) . "' is not a string",
            ],
            'invalid words argument' => [
                'value' => 'some string',
                'words' => 'this is not valid',
                'replacement' => '',
                'exception' => FilterException::class,
                'message' => 'Words was not an array or a callable that returns an array',
            ],
            'invalid return from callable words argument' => [
                'value' => 'some string',
                'words' => function () {
                    return 'this is also not valid';
                },
                'replacement' => '',
                'exception' => FilterException::class,
                'message' => 'Words was not an array or a callable that returns an array',
            ],
        ];
    }

    /**
     * @param string $input         The string value to be filtered.
     * @param string $expectedValue The expected filtered value.
     *
     * @test
     * @dataProvider provideStripEmoji
     */
    public function stripEmoji(string $input, string $expectedValue): void
    {
        $actualValue = Strings::stripEmoji($input);
        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @return array
     */
    public static function provideStripEmoji(): array
    {
        return [
            'mulitple emoji' => [
                'input' => 'This 💩 text contains 😞 multiple emoji 🍔 characters 🍚. As well as an alphanumeric '
                . 'supplement 🆗 and flag 🚩',
                'expected' => 'This  text contains  multiple emoji  characters . As well as an alphanumeric '
                . 'supplement  and flag ',
            ],
            'emoji' => [
                'input' => '🙄 this is ridiculous',
                'expected' => ' this is ridiculous',
            ],
            'alphanumeric supplement' => [
                'input' => 'Contains a 🆗 character',
                'expected' => 'Contains a  character',
            ],
            'flag/transportation symbols' => [
                'input' => 'Contains a 🚩 character',
                'expected' => 'Contains a  character',
            ],
            'dingbat symbols' => [
                'input' => 'Contains a ❗ character',
                'expected' => 'Contains a  character',
            ],
        ];
    }
}
