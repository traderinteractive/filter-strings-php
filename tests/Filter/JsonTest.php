<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;
use TraderInteractive\Exceptions\FilterException;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Json
 * @covers ::<private>
 */
final class JsonFilterTest extends TestCase
{
    /**
     * @test
     * @covers ::parse
     * @dataProvider provideParse
     *
     * @param string $value    The value to filter.
     * @param mixed  $expected The expected result.
     */
    public function parse(string $value, $expected)
    {
        $result = Json::parse($value);

        $this->assertSame($expected, $result);
    }

    /**
     * @return array
     */
    public function provideParse() : array
    {
        return [
            'json' => [
                'value' => '{"a":"b","c":[1,2,[3],{"4":"d"}], "e":   "f"}',
                'expected' => [
                    'a' => 'b',
                    'c' => [
                        1,
                        2,
                        [3],
                        [4 => 'd'],
                    ],
                    'e' => 'f',
                ],
            ],
            'null string' => [
                'value' => 'null',
                'expected' => null,
            ],
            'integer string' => [
                'value' => '1',
                'expected' => 1,
            ],
            'float string' => [
                'value' => '0.000001',
                'expected' => 0.000001,
            ],
            'double string' => [
                'value' => '1.56e10',
                'expected' => 1.56e10,
            ],
            'true string' => [
                'value' => 'true',
                'expected' => true,
            ],
            'false string' => [
                'value' => 'false',
                'expected' => false,
            ],
        ];
    }

    /**
     * @test
     * @covers ::parse
     */
    public function parseNullWithAllowNull()
    {
        $value = null;
        $result = Json::parse($value, true);

        $this->assertSame($value, $result);
    }

    /**
     * @test
     * @covers ::parse
     * @dataProvider provideInvalidJSON
     *
     * @param mixed  $value   The value to filter.
     * @param string $message The expected error message.
     */
    public function parseThrowsException($value, string $message)
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage($message);

        Json::parse($value);
    }

    /**
     * @test
     * @covers ::parse
     */
    public function parseThrowsExceptionOnNull()
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(Json::ERROR_CANNOT_BE_NULL);

        Json::parse(null);
    }

    /**
     * @test
     * @covers ::parse
     */
    public function parseThrowsExceptionForRecursionDepth()
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(sprintf(Json::ERROR_INVALID_JSON, 'Maximum stack depth exceeded'));

        Json::parse('[[]]', false, 1);
    }

    /**
     * @test
     * @covers ::validate
     * @dataProvider provideValidate
     *
     * @param string $value The value to filter.
     */
    public function validate(string $value)
    {
        $result = Json::validate($value);

        $this->assertSame($value, $result);
    }

    /**
     * @return array
     */
    public function provideValidate() : array
    {
        return [
            'json' => ['{"a":  "b",  "c":[1,  {"2": 3},[4]], "d": "e"}'],
            'null' => ['null'],
            'integer string' => ['12345'],
            'float string' => ['1.000003'],
            'double string' => ['445.2e100'],
            'true string' => ['true'],
            'false string' => ['false'],
        ];
    }

    /**
     * @test
     * @covers ::validate
     */
    public function validateNullWithAllowNull()
    {
        $value = null;
        $result = Json::validate($value, true);

        $this->assertSame($value, $result);
    }

    /**
     * @test
     * @covers ::validate
     * @dataProvider provideInvalidJSON
     *
     * @param mixed  $value   The value to filter.
     * @param string $message The expected error message.
     */
    public function validateThrowsException($value, string $message)
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage($message);

        Json::validate($value);
    }

    /**
     * @test
     * @covers ::validate
     */
    public function validateThrowsExceptionOnNull()
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(Json::ERROR_CANNOT_BE_NULL);

        Json::validate(null);
    }

    /**
     * @test
     * @covers ::validate
     */
    public function validateThrowsExceptionForRecursionDepth()
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(sprintf(Json::ERROR_INVALID_JSON, 'Maximum stack depth exceeded'));

        Json::validate('[[]]', false, 1);
    }

    /**
     * @return array
     */
    public function provideInvalidJSON() : array
    {
        return [
            'not a string' => [
                'value' => [],
                'message' => sprintf(Json::ERROR_NOT_A_STRING, var_export([], true)),
            ],
            'empty string' => [
                'value' => '',
                'message' => sprintf(Json::ERROR_INVALID_JSON, 'Syntax error'),
            ],
            'only whitespace' => [
                'value' => '     ',
                'message' => sprintf(Json::ERROR_INVALID_JSON, 'Syntax error'),
            ],
            'non-json string' => [
                'value' => 'some string',
                'message' => sprintf(Json::ERROR_INVALID_JSON, 'Syntax error'),
            ],
            'invalid json' => [
                'value' => '{"incomplete":',
                'message' => sprintf(Json::ERROR_INVALID_JSON, 'Syntax error'),
            ],
            'unpaired UTF-16 surrogate' => [
                'value' => '["\uD834"]',
                'message' => sprintf(Json::ERROR_INVALID_JSON, 'Single unpaired UTF-16 surrogate in unicode escape'),
            ],
        ];
    }
}
