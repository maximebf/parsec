<?php

namespace Parsec\Tests;

use Parsec\Lexer;

class LexerTest extends ParsecTestCase
{
    public function setUp()
    {
        $this->tokens = array(
            'number' => '[0-9]+',
            'plus' => '\+',
            'minus' => '-'
        );
    }

    public function testTokenize()
    {
        $lexer = new Lexer($this->tokens);
        $tokens = $lexer->tokenize('3 + 4 - 10');
        $this->assertCount(5, $tokens);
        foreach ($tokens as $token) {
            $this->assertInternalType('array', $token);
        }
        $this->assertEquals('number', $tokens[0]['token']);
        $this->assertEquals('3', $tokens[0]['value']);
        $this->assertEquals(0, $tokens[0]['position']['offset']);
        $this->assertEquals('plus', $tokens[1]['token']);
        $this->assertEquals('+', $tokens[1]['value']);
        $this->assertEquals(2, $tokens[1]['position']['offset']);
        $this->assertEquals('number', $tokens[2]['token']);
        $this->assertEquals('4', $tokens[2]['value']);
        $this->assertEquals(4, $tokens[2]['position']['offset']);
        $this->assertEquals('minus', $tokens[3]['token']);
        $this->assertEquals('-', $tokens[3]['value']);
        $this->assertEquals(6, $tokens[3]['position']['offset']);
        $this->assertEquals('number', $tokens[4]['token']);
        $this->assertEquals('10', $tokens[4]['value']);
        $this->assertEquals(8, $tokens[4]['position']['offset']);
    }

    public function testTokenizeWithWhitespace()
    {
        $lexer = new Lexer($this->tokens);
        $lexer->setIgnoreWhitespace(false);
        $tokens = $lexer->tokenize('3 + 4 - 10');
        $this->assertCount(9, $tokens);
        $this->assertInternalType('array', $tokens[0]);
        $this->assertInternalType('string', $tokens[1]);
    }
}
