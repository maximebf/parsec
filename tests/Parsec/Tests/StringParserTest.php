<?php

namespace Parsec\Tests;

use Parsec\StringParser;

class StringParserTest extends ParsecTestCase
{
    public function setUp()
    {
        $this->lexer = new \Parsec\Lexer(array(
            'number' => '[0-9]+',
            'plus' => '\+',
            'minus' => '-'
        ));
    }

    public function testTraversing()
    {
        $parser = new StringParser($this->lexer);
        $parser->tokenize('3 + 4 - 5');

        $this->assertEquals(-1, $parser->getCursorPosition());
        $parser->seek(0);
        $this->assertEquals(0, $parser->getCursorPosition());
        $parser->skipNext();
        $this->assertEquals(1, $parser->getCursorPosition());
        $this->assertEquals('+', $parser->getCurrentTokenValue());
        $parser->rewind();
        $this->assertEquals(0, $parser->getCursorPosition());
        $this->assertEquals('3', $parser->getCurrentTokenValue());
        $parser->skipUntil('minus');
        $this->assertEquals(3, $parser->getCursorPosition());
        $this->assertEquals('-', $parser->getCurrentTokenValue());
        $this->assertTrue($parser->hasMoreTokens());
        $parser->seek(5);
        $this->assertFalse($parser->hasMoreTokens());
        $parser->seek(0);
        $this->assertTrue($parser->isNextToken('plus'));
        $this->assertTrue($parser->isNextToken('number', array('plus')));
        $this->assertEquals('4', $parser->findNextTokenValue('number'));
    }
}
