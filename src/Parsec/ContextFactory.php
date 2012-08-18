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

use ReflectionClass;

/**
 * Creates an object of the specified class under one
 * of the registered namespace
 */
class ContextFactory
{
    /** @var array */
    protected $namespaces = array();

    /** @var string */
    protected $namespaceSeparator = '\\';
    
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
        foreach ($this->namespaces as $namespace) {
            $fqClassname = $namespace . $this->namespaceSeparator . $className;
            if (class_exists($fqClassname)) {
                $class = new ReflectionClass($fqClassname);
                return $class->newInstanceArgs($constructorArgs);
            }
        }
        return null;
    }

    /**
     * Sets the namespace separator
     * 
     * @param string $separator
     */
    public function setNamespaceSeparator($separator)
    {
        $this->namespaceSeparator = $separator;
    }

    /**
     * Returns the namespace separator
     * 
     * @return string
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }
    
    /**
     * Resets the namespaces
     * 
     * @param array $namespaces
     * @return ContextFactory
     */
    public function setNamespaces(array $namespaces)
    {
        $this->namespaces = array();
        array_map(array($this, 'addNamespace'), $namespaces);
        return $this;
    }
    
    /**
     * Adds a new namespace to search contexts in
     * 
     * @param string $namespace
     * @return ContextFactory
     */
    public function addNamespace($namespace)
    {
        $this->namespaces[] = trim((string) $namespace, $this->namespaceSeparator);
        return $this;
    }
    
    /**
     * Returns all namespaces
     * 
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }
}
