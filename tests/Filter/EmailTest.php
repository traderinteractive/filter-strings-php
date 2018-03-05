<?php

namespace TraderInteractive\Filter;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \TraderInteractive\Filter\Email
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
     * @expectedException \TraderInteractive\Exceptions\FilterException
     * @expectedExceptionMessage Value '@email.com' is not a valid email
     * @covers ::filter
     */
    public function filterNotValid()
    {
        Email::filter('@email.com');
    }
}
