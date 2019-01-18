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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value '1' is not a string
     * @covers ::filter
     */
    public function filterNonString()
    {
        Url::filter(1);
    }

    /**
     * @test
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value 'www.example.com' is not a valid url
     * @covers ::filter
     */
    public function filterNotValid()
    {
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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value failed filtering, $allowNull is set to false
     * @covers ::filter
     */
    public function filterNullFail()
    {
        Url::filter(null);
    }
}
