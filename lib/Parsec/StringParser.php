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
 * String parser using AbstractParser and Lexer
 */
class StringParser extends AbstractParser
{
    const TOKEN_EOS = 'eos';
    const TOKEN_TEXT = 'text';
    
	/** @var Lexer */
	protected $_lexer;
	
	/** @var array */
	protected $_tokens = array();
	
	/** @var int */
	protected $_position = 0;
	
	/** @var int */
	protected $_count = 0;
	
	/**
	 * @param Lexer $lexer
	 * @param ContextFactory $contextFactory
	 */
	public function __construct(Lexer $lexer = null, ContextFactory $contextFactory = null)
	{
	    $this->_lexer = $lexer;
		$this->_contextFactory = $contextFactory;
	}
	
	/**
	 * @param Lexer $lexer
	 */
	public function setLexer(Lexer $lexer)
	{
		$this->_lexer = $lexer;
	}
	
	/**
	 * @return Lexer
	 */
	public function getLexer()
	{
		return $this->_lexer;
	}
	
	/**
	 * Parse the specified string. 
	 * Must specify the starting context
	 *
	 * @param string $data
	 * @param string $context
	 * @param array $params Context parameters
	 * @return mixed Data returned by the context
	 */
	public function parse($string, $context, array $params = array())
	{
		$this->_data = $string;
		$this->_tokens = $this->_lexer->tokenize($string);
		$this->_position = 0;
		$this->_count = count($this->_tokens);
		
		return $this->enterContext($context, $params);
	}
	
	/**
	 * @param string $context
	 * @param array $params Context parameters
	 * @return mixed Data returned by context
	 */
	public function enterContext($contextName, array $params = array())
	{
		$context = $this->createContextInstance($contextName, array($this, $params));
		
		while (($i = $this->_position++) <= $this->_count) {
		    $token = null; $value = null;
		    
		    if ($i == $this->_count) {
		        $token = self::TOKEN_EOS;
		    } else if (is_string($this->_tokens[$i])) {
		        $token = self::TOKEN_TEXT;
		        $value = $this->_tokens[$i];
		    } else {
		        $token = $this->_tokens[$i]['token'];
		        $value = $this->_tokens[$i]['value'];
		    }
		    
		    if ($context->execute($token, $value)) {
		        break;
		    }
		}
		
		return $context->getExitData();
	}
}
