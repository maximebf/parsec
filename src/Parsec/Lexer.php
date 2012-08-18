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
 * Returns a tokenized representation of a string
 */
class Lexer
{
    /** @var string */
    protected $data;
    
    /** @var int */
    protected $offset;
    
    /** @var int */
    protected $length;
    
    /** @var array */
    protected $tokens;
    
    /** @var array */
    protected $result;
    
    /** @var bool */
    protected $ignoreWhitespace = true;
    
    /** @var array */
    protected $lineBreaks = array();
    
    /** @var string */
    protected $filename;
    
    /**
     * @param array $tokens
     */
    public function __construct(array $tokens = array())
    {
        $this->tokens = $tokens;
    }
    
    /**
     * @param array $tokens
     * @return Lexer
     */
    public function setTokens(array $tokens)
    {
        $this->tokens = $tokens;
        return $this;
    }
    
    /**
     * @param string $name
     * @param string $regexp
     * @return Lexer
     */
    public function addToken($name, $regexp)
    {
        if (is_array($name)) {
            foreach ($name as $n => $r) {
                $this->addToken($n, $r);
            }
            return;
        }
        
        $this->tokens[$name] = $regexp;
        return $this;
    }
    
    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }
    
    /**
     * @param bool $ignore
     * @return Lexer
     */
    public function setIgnoreWhitespace($ignore = true)
    {
        $this->ignoreWhitespace = $ignore;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isWhitespaceIgnored()
    {
        return $this->ignoreWhitespace;
    }
    
    /**
     * Parse the specified string and returns an array of tokens
     *
     * @param string $string
     * @param string $filename
     * @return array
     */
    public function tokenize($string, $filename = null)
    {
        $this->data = $string;
        $this->offset = 0;
        $this->length = strlen($string);
        $this->filename = $filename;
        $this->lineBreaks = array_map(function($v) { return $v[1]; }, 
        	preg_split("/\n/", $string, -1, PREG_SPLIT_OFFSET_CAPTURE));
          
        $tokens = array();
        while ($this->offset <= $this->length) {
            // search for the next token
            list($token, $value, $text, $offset) = $this->gotoNextToken();
            if (!empty($text)) {
                $tokens[] = $text;
            }
            
            if ($token !== null) {
                $tokens[] = array(
                    'token' => $token, 
                    'value' => $value, 
                    'position' => $this->getPosition($offset)
                );
            }
        }
        
        return $tokens;
    }
    
    /**
     * Goto the next token and return the text between the last token and the newly found one
     *
     * @return array
     */
    public function gotoNextToken()
    {
        list($token, $value, $nextOffset) = $this->getNextToken();
        
        $text = substr($this->data, $this->offset, $nextOffset - $this->offset);
        $this->offset = $nextOffset + (strlen($value) ?: 1);
        
        if ($this->ignoreWhitespace) {
            $text = trim($text);
        }
        
        return array($token, $value, $text, $nextOffset);
    }
    
    /**
     * Get the next token
     *
     * @return array
     */
    public function getNextToken()
    {
        $token = null;
        $value = null;
        $nextOffset = $this->length;
        foreach ($this->tokens as $name => $regexp) {
            if(preg_match('/' . $regexp . '/m', $this->data, $matches, PREG_OFFSET_CAPTURE, $this->offset)) {
                if ($nextOffset > $matches[0][1]) {
                    $token = $name;
                    $value = $matches[0][0];
                    $nextOffset = $matches[0][1];
                }
            }
        }
        return array($token, $value, $nextOffset);
    }
    
    /**
     * Returns the position in the file from the offset
     * 
     * @param string $offset
     * @return array
     */
    protected function getPosition($offset)
    {
        $previousBreak = 0;
        $breakOffset = 0;
        foreach ($this->lineBreaks as $line => $breakOffset) {
            if ($offset > $previousBreak && $offset <= $breakOffset) {
                break;
            }
            $previousBreak = $breakOffset;
        }
        return array(
           'line' => $line,
           'character' => $offset - $previousBreak,
           'offset' => $offset,
           'file' => $this->filename
        );
    }
}
