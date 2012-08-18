<?php

include __DIR__ . '/tests/bootstrap.php';

class ArithParser extends Parsec\StringParser
{
    public function __construct()
    {
        $factory = new Parsec\ContextFactory(array('\\'));
        $lexer = new Parsec\Lexer(array(
            'number' => '[0-9]+',
            'plus' => '\+',
            'minus' => '\-',
            'multi' => '\*',
            'div' => '\/',
            'bracketOpen' => '\(',
            'bracketClose' => '\)'
        ));
        
        parent::__construct($lexer, $factory);
    }
    
    public function parse($string)
    {
        return parent::parse($string, 'Expression');
    }
}

class Expression extends Parsec\Context
{
    protected $number;
    
    public function tokenNumber($value)
    {
        $this->number = $value;
    }
    
    public function tokenPlus()
    {
        $this->exitContext($this->number + $this->enterContext('Expression'));
    }
    
    public function tokenMinus()
    {
        $this->exitContext($this->number - $this->enterContext('Expression'));
    }
    
    public function tokenMulti()
    {
        $this->exitContext($this->number * $this->enterContext('Expression'));
    }
    
    public function tokenDiv()
    {
        $this->exitContext($this->number / $this->enterContext('Expression'));
    }
    
    public function tokenBracketOpen()
    {
        if ($this->number === null) {
            $this->number = 1;
        }
        $this->exitContext($this->number * $this->enterContext('Expression'));
    }
    
    public function tokenBracketClose()
    {
        $this->exitContext($this->number);
    }
    
    public function tokenEos($text)
    {
        $this->exitContext($this->number);
    }
}

$parser = new ArithParser();
printf("3 + 4 = %s\n", $parser->parse('3 + 4'));
printf("6 * (2 / 3) = %s\n", $parser->parse('6 * (2 / 3)'));
