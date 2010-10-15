<?php
/**
 * Parsec
 * Copyright (c) 2010 Maxime Bouroumeau-Fuseau
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Maxime Bouroumeau-Fuseau
 * @copyright 2010 (c) Maxime Bouroumeau-Fuseau
 * @license http://www.opensource.org/licenses/mit-license.php
 * @link http://github.com/maximebf/parsec
 */
 
namespace Parsec;

/**
 * Returns a tokenized representation of a string
 */
class Lexer
{
    /** @var string */
    protected $_data;
    
	/** @var int */
	protected $_offset;
	
	/** @var int */
	protected $_length;
	
	/** @var array */
	protected $_tokens;
	
	/** @var array */
	protected $_result;
	
	/** @var bool */
	protected $_ignoreWhitespace = true;
	
	/** @var array */
	protected $_lineBreaks = array();
	
	/** @var string */
	protected $_filename;
	
	/**
	 * @param array $tokens
	 */
	public function __construct(array $tokens = array())
	{
	    $this->_tokens = $tokens;
	}
	
	/**
	 * @param array $tokens
	 * @return Lexer
	 */
	public function setTokens(array $tokens)
	{
	    $this->_tokens = $tokens;
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
	    
	    $this->_tokens[$name] = $regexp;
	    return $this;
	}
	
	/**
	 * @return array
	 */
	public function getTokens()
	{
	    return $this->_tokens;
	}
	
	/**
	 * @param bool $ignore
	 * @return Lexer
	 */
	public function setIgnoreWhitespace($ignore = true)
	{
	    $this->_ignoreWhitespace = $ignore;
	    return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isWhitespaceIgnored()
	{
	    return $this->_ignoreWhitespace;
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
		$this->_data = $string;
		$this->_offset = 0;
		$this->_length = strlen($string);
        $this->_filename = $filename;
		$this->_lineBreaks = array_map(function($v) { return $v[1]; }, 
		  preg_split("/\n/", $string, -1, PREG_SPLIT_OFFSET_CAPTURE));
		  
		$tokens = array();
		while ($this->_offset <= $this->_length) {
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
		
		$text = substr($this->_data, $this->_offset, $nextOffset - $this->_offset);
		$this->_offset = $nextOffset + (strlen($value) ?: 1);
		
		if ($this->_ignoreWhitespace) {
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
		$nextOffset = $this->_length;
		foreach ($this->_tokens as $name => $regexp) {
			if(preg_match('/' . $regexp . '/m', $this->_data, $matches, PREG_OFFSET_CAPTURE, $this->_offset)) {
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
	    foreach ($this->_lineBreaks as $line => $breakOffset) {
	        if ($offset > $previousBreak && $offset <= $breakOffset) {
	            break;
	        }
	        $previousBreak = $breakOffset;
	    }
	    return array(
	       'line' => $line,
	       'character' => $offset - $previousBreak,
	       'offset' => $offset,
	       'file' => $this->_filename
	    );
	}
}
