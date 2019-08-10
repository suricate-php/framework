<?php

use Suricate\Session;
use Suricate\Suricate;

/**
 * @SuppressWarnings("StaticAccess")
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    public function testNative()
    {
        new \Suricate\Suricate([], './tests/stubs/session.ini');
        $this->assertInstanceOf(
            Session\Native::class,
            Suricate::Session()->getInstance()
        );
        $this->assertSame(session_id(), Suricate::Session()->getId());
        $oldSessionId = session_id();
        Suricate::Session()->regenerate();
        $newSessionId = session_id();
        $this->assertNotEquals($oldSessionId, $newSessionId);

        Suricate::Session()->write("key", "my value");
        $this->assertSame("my value", Suricate::Session()->read("key"));

        Suricate::Session()->write("key", 1);
        $this->assertSame(1, Suricate::Session()->read("key"));

        Suricate::Session()->destroy("key");
        $this->assertNull(Suricate::Session()->read("key"));

        $oldSessionId = session_id();
        Suricate::Session()->write("key", 1);
        Suricate::Session()->close();
        $newSessionId = session_id();
        $this->assertNull(Suricate::Session()->read("key"));
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }
}
