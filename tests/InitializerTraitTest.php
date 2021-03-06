<?php

namespace atk4\core\tests;

use atk4\core;
use atk4\core\Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \atk4\core\InitializerTrait
 */
class InitializerTraitTest extends TestCase
{
    /**
     * Test constructor.
     */
    public function testBasic()
    {
        $m = new ContainerMock2();
        $i = $m->add(new InitializerMock());

        $this->assertEquals(true, $i->result);
    }

    public function testInitializerNotCalled()
    {
        $this->expectException(Exception::class);
        $m = new ContainerMock2();
        $m->add(new BrokenInitializerMock());
    }

    public function testInitializedTwice()
    {
        $this->expectException(Exception::class);
        $m = new InitializerMock();
        $m->init();
        $m->init();
    }
}

// @codingStandardsIgnoreStart
class ContainerMock2
{
    use core\ContainerTrait;
}

class _InitializerMock
{
    use core\InitializerTrait;
}

class InitializerMock extends _InitializerMock
{
    public $result = false;

    public function init()
    {
        parent::init();

        $this->result = true;
    }
}

class BrokenInitializerMock extends _InitializerMock
{
    public function init()
    {
        // do not call parent
    }
}
// @codingStandardsIgnoreEnd
