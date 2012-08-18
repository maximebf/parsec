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
 * A contexts handles the parsing of a group of tokens.
 *
 * Contexts can branch out and use other contexts to parse parts of the
 * tokens array. This allows to organize your parser into a logical flow.
 * Contexts can be recursive and can returns data.
 */
abstract class Context
{
    /** @var AbstractParser */
    protected $parser;
    
    /** @var array */
    protected $params;

    /** @var Context */
    protected $parentContext;
    
    /** @var array */
    protected $exitData;
    
    /** @var bool */
    protected $exitContext;
    
    /** @var string */
    protected $currentToken;
    
    /** @var array */
    protected $currentTokenPosition;
    
    /**
     * @param AbstractParser $parser
     */
    public function __construct(AbstractParser $parser, array $params = array(), Context $parentContext = null)
    {
        $this->parser = $parser;
        $this->params = $params;
        $this->parentContext = $parentContext;
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
     * @param mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        if (!$this->hasParam($name)) {
            return $default;
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
     * @return Context
     */
    public function getParentContext()
    {
        return $this->parentContext;
    }
    
    /**
     * @param string $context
     * @param array $params Context parameters
     * @return mixed Data returned by context
     */
    public function enterContext($context, array $params = array())
    {
        return $this->parser->enterContext($context, $params, $this);
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
     * Execute the action associated to a token
     * 
     * @param string $token
     * @param string $data
     * @param array $position
     * @return mixed
     */
    public function execute($token, $data = null, $position = null)
    {
        $this->currentToken = $token;
        $this->currentTokenPosition = $position;
        
        $method = 'token' . ucfirst($token);
        if (!method_exists($this, $method) && !method_exists($this, '__call')) {
            return null;
        }
        
        $this->_beforeToken();
        $this->{$method}($data);
        $this->_afterToken();
        
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
    protected function _beforeToken()
    {
    }
    
    /**
     * Method called after a specific token method
     */
    protected function _afterToken()
    {
    }
    
    /**
     * Throws a syntax error exception
     * 
     * @param string $token
     * @param array $position
     */
    protected function syntaxError($token = null, $position = null)
    {
        $token = $token ?: $this->currentToken;
        $position = $position ?: $this->currentTokenPosition;
        
        if ($position !== null) {
            $position = " at character {$position['character']} in line {$position['line']} in '{$position['file']}'";
        }
        
        throw new SyntaxException("Syntax error, unexpected token '$token'$position");
    }
}
