<?php
/**
 * Harmony
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
 * @link http://github.com/maximebf
 */
 
namespace ParseInContext;

/**
 * Parses a string according to tokens
 */
class StringParser extends AbstractParser
{	
	/** @var int */
	protected $offset;
	
	/** @var int */
	protected $length;
	
	/** @var bool */
	protected $autoTrim = false;
	
	/**
	 * Parse the specified string. 
	 * Must specify the starting context
	 *
	 * @param string $data
	 * @param string $context
	 * @param array $params Context parameters
	 * @return mixed Data returned by the context
	 */
	public function parse($string, $context, $params = array())
	{
		$this->data = $string;
		$this->offset = 0;
		$this->length = strlen($string);
		
		return $this->enterContext($context, $params);
	}
	
	/**
	 * @param string $context
	 * @param array $params Context parameters
	 * @return mixed Data returned by context
	 */
	public function enterContext($context, array $params = array())
	{
		$instance = $this->createContextInstance($context, array($this, $params));
		
		// parser loop
		while ($this->offset <= $this->length) {
			// search for the next token
			list($token, $text) = $this->gotoNextToken($instance->getTokens());
			// execute action for the token
			$contextEnd = $instance->execute($token, $text);
			if ($contextEnd) {
				break;
			}
		}
		
		$data = $instance->getExitData();
		return $data;
	}
	
	/**
	 * Goto the next token and return the text between the last token and the newly found one
	 *
	 * @return array An array with the token name as first element and the text as second
	 */
	public function gotoNextToken($additionalTokens = array())
	{
		list($token, $nextOffset) = $this->getNextToken($additionalTokens);
		
		$text = substr($this->data, $this->offset, $nextOffset - $this->offset);
		$this->offset = $nextOffset + 1;
		
		if ($this->autoTrim) {
		    $text = trim($text);
		}
		
		return array($token, $text);
	}
	
	/**
	 * Get the next token
	 *
	 * @return array Array with the token name as the first element and the offset as the second
	 */
	public function getNextToken($additionalTokens = array())
	{
		$tokens = array_merge($this->tokens, $additionalTokens);
		$token = self::EOF;
		$nextOffset = $this->length;
		foreach ($tokens as $name => $regexp) {
			if(preg_match('/' . $regexp . '/m', $this->data, $matches, PREG_OFFSET_CAPTURE, $this->offset)) {
				if ($nextOffset > $matches[0][1]) {
					$token = $name;
					$nextOffset = $matches[0][1];
				}
			}
		}
		return array($token, $nextOffset);
	}
	
	/**
	 * @param int $offset
	 */
	public function _setOffset($offset)
	{
		if ($offset > $this->length) {
			throw new ParserException('Offset must be less than the length');
		}
		$this->offset = $offset;
	}
	
	/**
	 * @return int
	 */
	public function _getOffset()
	{
		return $this->offset;
	}
	
	/**
	 * Returns the length of the string being parsed
	 *
	 * @return int
	 */
	public function _getLength()
	{
		return $this->length;
	}
}
