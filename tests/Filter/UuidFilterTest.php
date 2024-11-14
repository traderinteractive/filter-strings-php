<?php

namespace Filter;

use InvalidArgumentException;
use TraderInteractive\Exceptions\FilterException;
use TraderInteractive\Filter\UuidFilter;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\UuidFilter
 * @covers ::<private>
 */
final class UuidFilterTest extends TestCase
{
    /**
     * @var string
     * @internal
     */
    const UUID_V1 = '1a42403c-a29d-11ef-b864-0242ac120002';

    /**
     * @var string
     * @internal
     */
    const UUID_V4 = 'cc468b36-0b9d-4c93-b8e9-d5e949331ffb';

    /**
     * @var string
     * @internal
     */
    const UUID_V7 = '01932b4a-af2b-7093-af59-2fb2044d13d8';

    /**
     * @test
     * @covers ::filter
     */
    public function filterUuidV1()
    {
        $this->assertSame(self::UUID_V1, UuidFilter::filter(self::UUID_V1));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterUuidV4()
    {
        $this->assertSame(self::UUID_V4, UuidFilter::filter(self::UUID_V4));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterUuidV7()
    {
        $this->assertSame(self::UUID_V7, UuidFilter::filter(self::UUID_V7));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullAllowedNullIsTrue()
    {
        $this->assertNull(UuidFilter::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullAllowedNullIsFalse()
    {
        $this->expectException(FilterException::class);
        UuidFilter::filter(null, false);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterWithInvalidVersionSpecified()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(UuidFilter::UNSUPPORTED_VERSION_ERROR_FORMAT, 0));
        UuidFilter::filter(self::UUID_V7, false, [0]);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterValueDoesNotMatchGivenVersions()
    {
        $this->expectException(FilterException::class);
        $this->expectExceptionMessage(
            sprintf(
                UuidFilter::FILTER_ERROR_FORMAT,
                self::UUID_V4,
                implode(', ', [1,7])
            )
        );
        UuidFilter::filter(self::UUID_V4, false, [1,7]);
    }
}
