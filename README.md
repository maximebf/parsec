# Parsec

**Contextual text parser in PHP 5.3.**

Parsec is a really simple parser toolkit which can be used to create small [DSL](http://en.wikipedia.org/wiki/Domain-specific_language) in PHP.

In the example below, a really simple arithmetic parser (no operator priority) will be created. 
The source code of this example is located in the demo folder.

An autoloader must be used for Parsec to work. A very basic one is included and can be registered using:

    require_once 'Parsec/ContextFactory.php';
    Parsec\ContextFactory::registerAutoloader();
    
(Include paths must be properly configure)

## Creating a lexer

A string must be tokenized using a [lexer](http://en.wikipedia.org/wiki/Lexical_analysis) before being parsed.
Lexers can be created using the Parsec\Lexer class. Tokens are defined as an associative array where keys are their name and values are a regular expression without the delimiting characters (forward slash, you'll need to escape characters).

    $lexer = new Parsec\Lexer(array(
        'number' => '[0-9]+',
        'plus' => '\+',
        'minus' => '\-',
        'multi' => '\*',
        'div' => '\/',
        'bracketOpen' => '\(',
        'bracketClose' => '\)'
    ));
    
    $tokens = $lexer->tokenize($string);
    
The result of the last line is an array with each token represented as an associative array with a 'token' key and a 'value' key. Unmatched strings are added to the array as well.

## Creating a parser

As most of parsers are meant to parse text, Parsec includes the Parsec\StringParser class. It already provides all the needed code to loop through the token array. Parsers can also be created from the ground up using Parsec\AbstractParser.

In the following example, StringParser will be used. Configuring a lexer and a context factory can be done by overriding the constructor. A context factory defines in which namespaces to find context classes. It is also possible to override the parse method to specify the context in which to start.
    
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

## Creating contexts

Contexts are classes which inherit from Context and which will do something with the tokens. One method must be created for each tokens that need to be handled. These methods must start by "token" followed by the capitalized token name. They will receive the value of the token.

It is possible to enter a new context using the enterContext() method or exit the current one using exitContext().
This last method takes one argument which will be returned as the result of the context.

The "eos" token (End Of String) will be automatically appended to the tokens array. Unmatched string can be caught using the "text" token.

    class Expression extends Parsec\Context
    {
        protected $_number;
        
        public function tokenNumber($value)
        {
            $this->_number = $value;
        }
        
        public function tokenPlus()
        {
            $this->exitContext($this->_number + $this->enterContext('Expression'));
        }
        
        public function tokenMinus()
        {
            $this->exitContext($this->_number - $this->enterContext('Expression'));
        }
        
        public function tokenMulti()
        {
            $this->exitContext($this->_number * $this->enterContext('Expression'));
        }
        
        public function tokenDiv()
        {
            $this->exitContext($this->_number / $this->enterContext('Expression'));
        }
        
        public function tokenBracketOpen()
        {
            if ($this->_number === null) {
                $this->_number = 1;
            }
            $this->exitContext($this->_number * $this->enterContext('Expression'));
        }
        
        public function tokenBracketClose()
        {
            $this->exitContext($this->_number);
        }
        
        public function tokenEos($text)
        {
            $this->exitContext($this->_number);
        }
    }
    
## Using

    $parser = new ArithParser();
    printf("3 + 4 = %s\n", $parser->parse('3 + 4'));
    printf("6 * (2 / 3) = %s\n", $parser->parse('6 * (2 / 3)'));
    
