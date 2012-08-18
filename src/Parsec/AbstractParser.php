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
