<?php
/*
 * This file is part of the Parsec package.
 *
 * (c) 2012 Maxime Bouroumeau-Fuseau
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Parsec;

/**
 * String parser using AbstractParser and Lexer
 */
class StringParser extends AbstractParser
{
    const TOKEN_EOS = 'eos';
    const TOKEN_TEXT = 'text';
    
    /** @var Lexer */
    protected $lexer;
    
    /** @var array */
    protected $tokens = array();
    
    /** @var int */
    protected $cursor = 0;
    
    /** @var int */
    protected $count = 0;
    
    /**
     * @param Lexer $lexer
     * @param ContextFactory $contextFactory
     */
    public function __construct(Lexer $lexer = null, ContextFactory $contextFactory = null)
    {
        $this->lexer = $lexer;
        $this->contextFactory = $contextFactory;
    }
    
    /**
     * @param Lexer $lexer
     */
    public function setLexer(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }
    
    /**
     * @return Lexer
     */
    public function getLexer()
    {
        return $this->lexer;
    }
    
    /**
     * Parses the specified string. 
     * Must specify the starting context
     *
     * @param string $string
     * @param string $context
     * @param array $params Context parameters
     * @param string $filename
     * @return mixed Data returned by the context
     */
    public function parse($string, $context, array $params = array(), $filename = null)
    {
        $this->tokenize($string, $filename);
        return $this->enterContext($context, $params);
    }

    /**
     * Tokenizes the string
     *
     * @param string $string
     * @param string $filename
     */
    public function tokenize($string, $filename = null)
    {
        $this->data = $string;
        $this->tokens = $this->lexer->tokenize($string, $filename);
        $this->tokens[] = array('token' => self::TOKEN_EOS, 'value' => null, 'position' => null);
        $this->cursor = -1;
        $this->count = count($this->tokens);
    }
    
    /**
     * Enters the context with the specified name and processes each tokens
     * until the context exits
     * 
     * @param string $context
     * @param array $params Context parameters
     * @return mixed Data returned by context
     */
    public function enterContext($contextName, array $params = array(), Context $parentContext = null)
    {
        $context = $this->createContextInstance($contextName, array($this, $params, $parentContext));
        
        do {
            $this->cursor++;
            $token = null; $value = null; $position = null;
            
            if (is_string($this->tokens[$this->cursor])) {
                $token = self::TOKEN_TEXT;
                $value = $this->tokens[$this->cursor];
            } else {
                $token = $this->tokens[$this->cursor]['token'];
                $value = $this->tokens[$this->cursor]['value'];
                $position = $this->tokens[$this->cursor]['position'];
            }
            
            if ($context->execute($token, $value, $position)) {
                break;
            }

        } while ($this->cursor < $this->count);
        
        return $context->getExitData();
    }

    /**
     * Returns the position of the cursor
     * 
     * @return int
     */
    public function getCursorPosition()
    {
        return $this->cursor;
    }

    /**
     * Moves the cursor the the specified position
     * 
     * @param int $position
     * @return StringParser
     */
    public function seek($position)
    {
        $this->cursor = $position;
        return $this;
    }
    
    /**
     * Skips the next token
     * 
     * @param int $howMany
     * @return StringParser
     */
    public function skipNext($howMany = 1)
    {
        $this->cursor += $howMany;
        return $this;
    }
    
    /**
     * Skips tokens until the specified one
     * 
     * @param string $tokenName
     * @param int $step
     * @return StringParser
     */
    public function skipUntil($tokenName, $step = 1)
    {
        do {
            $this->cursor += $step;
        } while($this->cursor >= 0 && $this->cursor < $this->count && 
           !$this->isToken($this->tokens[$this->cursor], $tokenName));
        
        return $this;
    }

    /**
     * Rewinds the parser position
     *
     * @param int $howMany
     * @return StringParser
     */
    public function rewind($howMany = 1)
    {
        $this->cursor -= $howMany;
        return $this;
    }

    /**
     * Rewinds tokens until the specified one
     *
     * @param string $tokenName
     * @return StringParser
     */
    public function rewindUntil($tokenName)
    {
        return $this->skipUntil($tokenName, -1);
    }

    /**
     * Retuns the token at the current position
     * 
     * @return array
     */
    public function getCurrentToken()
    {
        return $this->tokens[$this->cursor];
    }

    /**
     * Returns the current token's value
     * 
     * @return mixed
     */
    public function getCurrentTokenValue()
    {
        return $this->tokens[$this->cursor]['value'];
    }

    /**
     * @return bool
     */
    public function hasMoreTokens()
    {
        return $this->cursor < $this->count - 1;
    }
    
    /**
     * Checks if the next token matches the specified one
     * 
     * @param string $token
     * @param array $ignore
     * @param int $direction
     * @return bool
     */
    public function isNextToken($tokenName, $ignore = array(), $direction = 1)
    {
        $i = $this->cursor;

        do {
            $i += $direction;
            if (!in_array($this->getTokenName($this->tokens[$i]), $ignore)) {
                break;
            }
        } while ($i >= 0 && $i < $this->count);
        
        return $this->isToken($this->tokens[$i], $tokenName);
    }

    /**
     * Checks if the previous token matches the specified one
     * 
     * @param string $token
     * @param array $ignore
     * @return bool
     */
    public function isPreviousToken($tokenName, $ignore = array())
    {
        return $this->isNextToken($tokenName, $ignore, -1);
    }
    
    /**
     * Returns the next token
     * 
     * @param bool $skip
     * @return array
     */
    public function getNextToken($skip = false)
    {
        $token = $this->tokens[$this->cursor + 1];
        if ($skip) {
            $this->cursor++;
        }
        return $token;
    }
    
    /**
     * Returns the value of the next token
     * 
     * @param bool $skip
     * @return string
     */
    public function getNextTokenValue($skip = false)
    {
        $token = $this->getNextToken($skip);
        return is_array($token) ? $token['value'] : $token;
    }
    
    /**
     * Returns the previous token
     * 
     * @param bool $rewind
     * @return array
     */
    public function getPreviousToken($rewind = false)
    {
        $token = $this->tokens[$this->cursor - 1];
        if ($rewind) {
            $this->cursor--;
        }
        return $token;
    }
    
    /**
     * Returns the value of the previous token
     * 
     * @param bool $rewind
     * @return string
     */
    public function getPreviousTokenValue($rewind = false)
    {
        $token = $this->getPreviousToken($rewind);
        return is_array($token) ? $token['value'] : $token;
    }

    /**
     * Finds the next token with the specified name
     * 
     * @param string $tokenName
     * @param integer $direction 1 to go forward, -1 to go backward
     * @return mixed
     */
    public function findNextTokenValue($tokenName, $direction = 1)
    {
        $i = $this->cursor;

        do {
            $i += $direction;
            if ($this->isToken($this->tokens[$i], $tokenName)) {
                break;
            }
        } while ($i >= 0 && $i < $this->count);
        
        return is_array($this->tokens[$i]) ? $this->tokens[$i]['value'] : $this->tokens[$i];
    }

    /**
     * Same as {@see findNextTokenValue()} but searching backward
     * 
     * @param string $tokenName
     * @return mixed
     */
    public function findPreviousTokenValue($tokenName)
    {
        return $this->findNextTokenValue($tokenName, -1);
    }
    
    /**
     * Checks if two tokens are equal
     * 
     * @param mixed $token1
     * @param string $token2
     * @return bool
     */
    public function isToken($token, $tokenName)
    {
        if (!is_array($token) && $tokenName == self::TOKEN_TEXT) {
            return true;
        }
        return $token['token'] == $tokenName;
    }
    
    /**
     * Returns the name of a token
     * 
     * @param mixed $token
     * @return string
     */
    public function getTokenName($token)
    {
        if (!is_array($token)) {
            return self::TOKEN_TEXT;
        }
        return $token['token'];
    }
}
