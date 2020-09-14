<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\FilterException;

/**
 * @coversDefaultClass \TraderInteractive\Filter\PhoneFilter
 * @covers ::__construct
 * @covers ::<private>
 */
final class PhoneFilterTest extends TestCase
{
    /**
     * @param mixed       $value         The value to be filtered.
     * @param bool        $allowNull     The allowNull value for the filter.
     * @param string      $format        The format of the filtered phone.
     * @param string|null $expectedValue The expected filtered value.
     *
     * @test
     * @covers ::filter
     * @dataProvider provideFilterData
     */
    public function filter($value, bool $allowNull, string $format, $expectedValue)
    {
        $actualValue = PhoneFilter::filter($value, $allowNull, $format);
        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @param mixed       $value         The value to be filtered.
     * @param bool        $allowNull     The allowNull value for the filter.
     * @param string      $format        The format of the filtered phone.
     * @param string|null $expectedValue The expected filtered value.
     *
     * @test
     * @covers ::__construct
     * @covers ::__invoke
     * @dataProvider provideFilterData
     */
    public function invoke($value, bool $allowNull, string $format, $expectedValue)
    {
        $filter = new PhoneFilter($allowNull, $format);
        $actualValue = $filter($value);
        $this->assertSame($expectedValue, $actualValue);
    }

    /**
     * @return array
     */
    public function provideFilterData() : array
    {
        return [
            ['2345678901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234 5678901', false, '({area}) {exchange}-{station}', '(234) 567-8901'],
            ['234 567-8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234 567.8901', false, '({area}) {exchange}-{station}', '(234) 567-8901'],
            ['234 567 8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234.5678901', false, '({area}) {exchange}-{station}', '(234) 567-8901'],
            ['234.567-8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234.567.8901', false, '({area}) {exchange}-{station}', '(234) 567-8901'],
            ['234.567 8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234-5678901', false, '({area}) {exchange}-{station}', '(234) 567-8901'],
            ['234-567-8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234-567.8901', false, '{area}-{exchange}-{station}', '234-567-8901'],
            ['234-567 8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234   -   567   -   8901', false, '({area}) {exchange}-{station}', '(234) 567-8901'],
            ['234   .   567   .   8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['234       567       8901', false, '{area}.{exchange}.{station}', '234.567.8901'],
            ['(234)-567-8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['(234).567.8901', false, '({area}) {exchange}-{station}', '(234) 567-8901'],
            ['(234) 567 8901', false, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, '2345678901'],
            ['     234-567-8901    ', false, '{exchange}-{station}', '567-8901'],
            [null, true, PhoneFilter::DEFAULT_FILTERED_PHONE_FORMAT, null],
        ];
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterWithAllowNull()
    {
        $value = null;
        $result = PhoneFilter::filter(null, true);

        $this->assertSame($value, $result);
    }

    /**
     * @param mixed $value The value to filter.
     *
     * @test
     * @covers ::filter
     * @dataProvider provideFilterThrowsException
     */
    public function filterThrowsException($value)
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(sprintf(PhoneFilter::ERROR_INVALID_PHONE_NUMBER, $value));

        PhoneFilter::filter($value);
    }

    /**
     * @return array
     */
    public function provideFilterThrowsException() : array
    {
        return [
            'empty string' => [''],
            'not all digits' => ['234-567a'],
            'not enough digits' => ['234567'],
            'too many digits' => ['23456789012'],
            'invalid exchange code' => ['123-4567'],
            'invalid area code' => ['123-234-5678'],
            'invalid separator' => ['234:567:8901'],
            'no opening parenthesis' => ['234) 567 8901'],
            'no closing parenthesis' => ['(234 567 8901'],
        ];
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterThrowsExceptionOnNonStringValues()
    {
        $value = ['foo' => 'bar'];
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(sprintf(PhoneFilter::ERROR_INVALID_PHONE_NUMBER, var_export($value, true)));

        PhoneFilter::filter($value);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterThrowsExceptionOnNull()
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(PhoneFilter::ERROR_VALUE_CANNOT_BE_NULL);

        PhoneFilter::filter(null);
    }
}
