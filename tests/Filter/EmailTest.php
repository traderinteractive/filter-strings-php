<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Email
 * @covers ::<private>
 */
final class EmailTest extends TestCase
{
    /**
     * @test
     * @covers ::filter
     */
    public function filter()
    {
        $email = 'first.last@email.com';
        $this->assertSame($email, Email::filter($email));
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNotString()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage("Value '111222333444' is not a string");
        Email::filter(111222333444);
    }

    /**
     * @test
     * @covers ::filter
     */
    public function filterNotValid()
    {
        $this->expectException(\TraderInteractive\Exceptions\FilterException::class);
        $this->expectExceptionMessage("Value '@email.com' is not a valid email");
        Email::filter('@email.com');
    }
}
