<?php

namespace TraderInteractive\Filter;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\FilterException;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Strings
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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value '1234' is not a string
     * @covers ::explode
     */
    public function explodeNonString()
    {
        Strings::explode(1234, '');
    }

    /**
     * Verifies explode filter with an empty delimiter.
     *
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Delimiter '''' is not a non-empty string
     * @covers ::explode
     */
    public function explodeEmptyDelimiter()
    {
        Strings::explode('test', '');
    }

    /**
     * @test
     * @covers ::stripTags
     */
    public function stripTagsFromNullReturnsNull()
    {
        $this->assertNull(Strings::stripTags(null));
    }

    /**
     * @test
     * @covers ::stripTags
     */
    public function stripTagsRemoveHtmlFromString()
    {
        $actual = Strings::stripTags('A string with <p>paragraph</p> tags');
        $expected = 'A string with paragraph tags';
        $this->assertSame($expected, $actual);
    }
}
