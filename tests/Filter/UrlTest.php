<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Url
 * @covers ::<private>
 */
final class UrlTest extends TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filter()
    {
        $url = 'http://www.example.com';
        $this->assertSame($url, Url::filter($url));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNonString()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage("Value '1' is not a string");
        Url::filter(1);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNotValid()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage("Value 'www.example.com' is not a valid url");
        Url::filter('www.example.com');
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullPass()
    {
        $this->assertSame(null, Url::filter(null, true));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNullFail()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage('Value failed filtering, $allowNull is set to false');
        Url::filter(null);
    }
}
