<?php

namespace Parsec\Tests;

use Parsec\ContextFactory;

class FakeContext {}

class ContextFactoryTest extends ParsecTestCase
{
    public function testNamespaces()
    {
        $factory = new ContextFactory();
        $factory->addNamespace('Parsec\Tests');
        $this->assertContains('Parsec\Tests', $factory->getNamespaces());

        $factory = new ContextFactory();
        $factory->setNamespaceSeparator('_');
        $factory->addNamespace('Parsec_Tests_');
        $this->assertContains('Parsec_Tests', $factory->getNamespaces());
    }

    public function testCreateInstance()
    {
        $factory = new ContextFactory(array('Parsec\Tests'));
        $c = $factory->createInstance('FakeContext');
        $this->assertInstanceOf('Parsec\Tests\FakeContext', $c);
    }
}
