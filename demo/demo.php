<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    get_include_path()
)));

require_once 'ParseInContext/ContextFactory.php';
\ParseInContext\ContextFactory::registerAutoloader();

class ArithParser extends \ParseInContext\StringParser
{
    protected $tokens = array(
        'plus' => '\+',
        'minus' => '\-',
        'multi' => '\*',
        'div' => '\/',
        'bracketOpen' => '\(',
        'bracketClose' => '\)'
    );
    
    protected $autoTrim = true;
    
    public function __construct()
    {
        parent::__construct(new \ParseInContext\ContextFactory(array('\\')));
    }
    
    public function parse($string)
    {
        return parent::parse($string, 'Expression');
    }
}

class Expression extends \ParseInContext\Context
{
    public function tokenPlus($text)
    {
        $this->exitContext($text + $this->enterContext('Expression'));
    }
    
    public function tokenMinus($text)
    {
        $this->exitContext($text - $this->enterContext('Expression'));
    }
    
    public function tokenMulti($text)
    {
        $this->exitContext($text * $this->enterContext('Expression'));
    }
    
    public function tokenDiv($text)
    {
        $this->exitContext($text / $this->enterContext('Expression'));
    }
    
    public function tokenBracketOpen($text)
    {
        if (empty($text)) {
            $text = 1;
        }
        $this->exitContext($text * $this->enterContext('Expression'));
    }
    
    public function tokenBracketClose($text)
    {
        $this->exitContext($text);
    }
    
    public function tokenEof($text)
    {
        $this->exitContext($text);
    }
}

$parser = new ArithParser();
printf("3 + 4 = %s\n", $parser->parse('3 + 4'));
printf("6 * (2 / 3) = %s\n", $parser->parse('6 * (2 / 3)'));

