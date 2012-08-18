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
            if (class_exists($namespace . $className)) {
                $class = new ReflectionClass($namespace . $className);
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
        $this->namespaces = array();
        array_map(array($this, 'addNamespace'), $namespaces);
        return $this;
    }
    
    /**
     * @param string $namespace
     * @return ContextFactory
     */
    public function addNamespace($namespace)
    {
        $this->namespaces[] = rtrim((string) $namespace, '\\') . '\\';
        return $this;
    }
    
    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }
}
