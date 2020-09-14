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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value failed filtering, $allowNull is set to false
     * @covers ::filter
     */
    public function filterNullFail()
    {
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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @covers ::filter
     */
    public function filterMinLengthFail()
    {
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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value 'a' with length '1' is less than '0' or greater than '0'
     * @covers ::filter
     */
    public function filterMaxLengthFail()
    {
        Strings::filter('a', false, 0, 0);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMinLengthNotInteger()
    {
        Strings::filter('a', false, -1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMaxLengthNotInteger()
    {
        Strings::filter('a', false, 1, -1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $minLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMinLengthNegative()
    {
        Strings::filter('a', false, -1);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $maxLength was not a positive integer value
     * @covers ::filter
     */
    public function filterMaxLengthNegative()
    {
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
     *
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value 'class@anonymous
     */
    public function filterWithObjectNoToStringMethod()
    {
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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage The value 'baz' was not found in the translation map array.
     */
    public function translateValueNotFoundInMap()
    {
        $map = ['foo' => '100', 'bar' => '200'];
        Strings::translate('baz', $map);
    }

    /**
     * @param string   $value          The value to be filtered.
     * @param array    $expectedResult The expected filter result.
     * @param string   $delimiter      The delimiter to use.
     * @param int|null $padLength      The array length of the result.
     * @param mixed    $padValue       The value to use when padding.
     * @param string   $padType        The direction to pad the resulting array.
     *
     * @test
     * @covers ::explode
     * @dataProvider provideExplodeData
     */
    public function explode(
        string $value,
        array $expectedResult,
        string $delimiter = ',',
        int $padLength = null,
        $padValue = null,
        string $padType = Strings::EXPLODE_PAD_RIGHT
    ) {
        $actualResult = Strings::explode($value, $delimiter, $padLength, $padValue, $padType);
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function provideExplodeData() : array
    {
        return [
            [
                'value' => 'a,bcd,e',
                'result' => ['a', 'bcd', 'e'],
            ],
            [
                'value' => 'a b c d,e',
                'result' => ['a', 'b', 'c', 'd,e'],
                'delimiter' => ' ',
            ],
            [
                'value' => 'a-b-c',
                'result' => ['a', 'b', 'c', null, null],
                'delimiter' => '-',
                'padLength' => 5,
                'padValue' => null,
                'padType' => Strings::EXPLODE_PAD_RIGHT,
            ],
            [
                'value' => 'a-b-c',
                'result' => [null, null, 'a', 'b', 'c'],
                'delimiter' => '-',
                'padLength' => 5,
                'padValue' => null,
                'padType' => Strings::EXPLODE_PAD_LEFT,
            ],
        ];
    }

    /**
     * @test
     * @covers ::explode
     */
    public function explodeWithInvalidPadType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid $padType value provided');
        Strings::explode('a,b,c', ',', 4, null, 'invalid');
    }

    /**
     * @test
     * @covers ::explode
     */
    public function explodeNonString()
    {
        $this->expectException(FilterException::class);
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
        $this->expectException(InvalidArgumentException::class);
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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     *
     * @return void
     */
    public function concatValueNotFilterable()
    {
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
}
