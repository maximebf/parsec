# ParseInContext

Allows to create contextual text parser in PHP 5.3. You can split the parsing in contexts, entering and living them at will.
You can even create recursive contexts. A context reacts to tokens.

The examples below create a really simple arithmetic parser (no operator priority). 
You can find the source code in the demo folder.

You must use an autoloader to use ParseInContext. You can use the one included (very basic) using:

    require_once 'ParseInContext/ContextFactory.php';
    \ParseInContext\ContextFactory::registerAutoloader();
    
(You must setup your include paths before using it)

## Creating a lexer

Before parsing a string, it must be tokenized first. This is done using a lexer.
Tokens are defined as an associative array where keys are token names and values are regular expression (without /).

    $lexer = new \ParseInContext\Lexer(array(
        'number' => '[0-9]+',
        'plus' => '\+',
        'minus' => '\-',
        'multi' => '\*',
        'div' => '\/',
        'bracketOpen' => '\(',
        'bracketClose' => '\)'
    ));

## Creating a parser

Create a class inherting from StringParser. 
Override the constructor to register a lexer and the namespace where your contexts are located.
Finally, override the parse method to specify the context in which to start.
    
    class ArithParser extends \ParseInContext\StringParser
    {
        public function __construct()
        {
            $factory = new \ParseInContext\ContextFactory(array('\\'));
            $lexer = new \ParseInContext\Lexer(array(
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

Contexts are classes which inherit from Context. Creates a method for each token you want to treat
in this context. Token's method must start with "token" follow by the capitalized token name. These
methods will receive the text between the previous token and the current one as their only argument.

You can enter a new context using the enterContext() method or exit the current one using exitContext().
This last method takes one argument which will be returned as the result for the context.

    class Expression extends \ParseInContext\Context
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
    
