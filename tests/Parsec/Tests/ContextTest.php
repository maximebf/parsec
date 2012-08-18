<?php

namespace Parsec\Tests;

use Parsec\Context,
    Parsec\StringParser;

class MockContext extends Context
{
    public $word;

    public function tokenWord($value)
    {
        $this->word = $value;
    }

    public function tokenDot()
    {
        $this->exitContext($this->word);
    }

    public function tokenComma()
    {
        $this->syntaxError();
    }
}

class ContextTest extends ParsecTestCase
{
    public function testParams()
    {
        $context = new MockContext(new StringParser(), array('foo' => 'bar'));
        $this->assertTrue($context->hasParam('foo'));
        $this->assertEquals('bar', $context->getParam('foo'));
        $this->assertNull($context->getParam('aaa'));
        $this->assertEquals('b', $context->getParam('aaa', 'b'));
        $this->assertArrayHasKey('foo', $context->getParams());
    }

    public function testExecute()
    {
        $context = new MockContext(new StringParser());
        $this->assertFalse($context->execute('word', 'foobar'));
        $this->assertTrue($context->execute('dot'));
        $this->assertEquals('foobar', $context->getExitData());
    }

    /**
     * @expectedException Parsec\SyntaxException
     */
    public function testSyntaxError()
    {
        $context = new MockContext(new StringParser());
        $context->execute('comma');
    }
}
