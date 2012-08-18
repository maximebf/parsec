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
 * Abstract class to build a parser using contexts to loop through the tokens
 */
abstract class AbstractParser
{
    /** @var ContextFactory */
    protected $contextFactory;
    
    /** @var mixed */
    protected $data;
    
    /**
     * @param ContextFactory $contextFactory
     */
    public function __construct(ContextFactory $contextFactory = null)
    {
        $this->contextFactory = $contextFactory;
    }
    
    /**
     * @param ContextFactory $factory
     */
    public function setContextFactory(ContextFactory $factory)
    {
        $this->contextFactory = $factory;
    }
    
    /**
     * @return ContectFactory
     */
    public function getContextFactory()
    {
        return $this->contextFactory;
    }
    
    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * @param string $context
     * @param array $params Context parameters
     * @return mixed Data returned by context
     */
    public abstract function enterContext($context, array $params = array());
    
    /**
     * Creates and returns a context object
     *
     * @param string $contextName
     * @param array $args
     * @return Context
     */
    public function createContextInstance($contextName, array $args)
    {
        return $this->getContextFactory()->createInstance($contextName, $args);
    }
}
