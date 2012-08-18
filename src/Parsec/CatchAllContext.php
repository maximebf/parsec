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
 * A context that will catch all uncatch tokens and appends them to $_value
 */
abstract class CatchAllContext extends Context
{
    /** @var string */
    protected $value = '';
    
    public function __call($method, $args)
    {
        $this->value .= $args[0];
    }
}
