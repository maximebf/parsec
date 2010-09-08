# ParseInContext

Allows to create contextual text parser in PHP 5.3. You can split the parsing in contexts, entering and living them at will.
You can even create recursive contexts. A context then reacts to tokens.

The examples below creates a really simple arithmetic parser (no operator priority). You can find the source code
in the demo folder.

You must use an autoloader to use ParseInContext. You can use the one included (very basic) using:

    require_once 'ParseInContext/ContextFactory.php';
    \ParseInContext\ContextFactory::registerAutoloader();
    
(You must setup your include paths before using it)

## Creating a parser and some tokens

Create a class inherting from StringParser. You can define tokens as an associative array where keys are token names
and values a regular expression (without /).
Override the constructor to register the namespace where your contexts are located.
Finally, override the parse method to specify the context in which to start.
    
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

## Creating contexts

Contexts are classes which inherit from Context. Creates a method for each token you want to treat
in this context. Token's method must start with "token" follow by the capitalized token name. These
methods will receive the text between the previous token and the current one as their only argument.

You can enter a new context using the enterContext() method or exit the current one using exitContext().
This last method takes one argument which will be returned as the result for the context.

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
    
## Using

    $parser = new ArithParser();
    printf("3 + 4 = %s\n", $parser->parse('3 + 4'));
    printf("6 * (2 / 3) = %s\n", $parser->parse('6 * (2 / 3)'));
    
