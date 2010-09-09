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
 * Creates an object of the specified class under one
 * of the registered namespace
 */
class ContextFactory
{
    /** @var array */
	protected $_namespaces = array();
	
	/**
	 * Registers a very simple autoloader which relies
	 * on include paths. Replaces namespace separators by DIRECTORY_SEPARATOR
	 */
	public static function registerAutoloader()
	{
	    spl_autoload_register(function($className) {
	        $filename = str_replace('\\', DIRECTORY_SEPARATOR, trim($className, '\\')) . '.php';
	        require_once $filename;
	    });
	}
	
	/**
	 * @param array $namespaces
	 */
	public function __construct(array $namespaces = array())
	{
	    $this->setNamespaces($namespaces);
	}
	
	/**
	 * @param string $className
	 * @param array $constructorArgs
	 * @return Context
	 */
	public function createInstance($className, array $constructorArgs = array())
	{
		foreach ($this->_namespaces as $namespace) {
			if (class_exists($namespace . $className)) {
				$class = new \ReflectionClass($namespace . $className);
				return $class->newInstanceArgs($constructorArgs);
			}
		}
		return null;
	}
	
	/**
	 * @param array $namespaces
	 * @return ContextFactory
	 */
	public function setNamespaces(array $namespaces)
	{
		$this->_namespaces = array();
		array_map(array($this, 'addNamespace'), $namespaces);
		return $this;
	}
	
	/**
	 * @param string $namespace
	 * @return ContextFactory
	 */
	public function addNamespace($namespace)
	{
		$this->_namespaces[] = rtrim((string) $namespace, '\\') . '\\';
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getNamespaces()
	{
		return $this->_namespaces;
	}
}
