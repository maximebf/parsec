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

abstract class Context
{
	/** @var AbstractParser */
	protected $parser;
	
	/** @var array */
	protected $tokens = array();
	
	/** @var array */
	protected $params;
	
	/** @var array */
	protected $exitData;
	
	/** @var bool */
	protected $exitContext;
	
	/**
	 * @param AbstractParser $parser
	 */
	public function __construct(AbstractParser $parser, $params = array())
	{
		$this->parser = $parser;
		$this->params = $params;
		$this->exitData = '';
		$this->exitContext = false;
		$this->init();
	}
	
	/**
	 * Context logic when initialized
	 */
	protected function init()
	{
	}
	
	/**
	 * @return Harmony_Parser_Abstract
	 */
	public function getParser()
	{
		return $this->parser;
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasParam($name)
	{
		return array_key_exists($name, $this->params);
	}
	
	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getParam($name)
	{
		if (!$this->hasParam($name)) {
			throw new ParserException('Missing parameter ' . $name . ' in context ' . get_class($this));
		}
		return $this->params[$name];
	}
	
	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}
	
	/**
	 * @param string $context
	 * @param array $params Context parameters
	 * @return mixed Data returned by context
	 */
	public function enterContext($context, array $params = array())
	{
	    return $this->getParser()->enterContext($context, $params);
	}
	
	/**
	 * Exit the context at the end of the current action
	 *
	 * @param mixed $data OPTIONAL
	 */
	public function exitContext($data = array())
	{
		$this->exitData = $data;
		$this->exitContext = true;
	}
	
	/**
	 * Get tokens for the current context
	 *
	 * @return array
	 */
	public function getTokens()
	{
		return $this->tokens;
	}
	
	/**
	 * Execute the action associated to a token
	 * 
	 * @param string $token
	 * @param string $data
	 * @return mixed
	 */
	public function execute($token, $data)
	{
		$method = 'token' . ucfirst($token);
		if (!method_exists($this, $method) && !method_exists($this, '__call')) {
			return null;
		}
		
		$this->beforeToken();
		$this->{$method}($data);
		$this->afterToken();
		
		return $this->exitContext;
	}
	
	/**
	 * @var mixed
	 */
	public function getExitData()
	{
		return $this->exitData;
	}
	
	/**
	 * Method called before a specific token method
	 */
	protected function beforeToken()
	{
	}
	
	/**
	 * Method called after a specific token method
	 */
	protected function afterToken()
	{
	}
	
	/**
	 * End of file reached by parser
	 */
	public function tokenEof($data)
	{
		$this->exitContext();
	}
}
