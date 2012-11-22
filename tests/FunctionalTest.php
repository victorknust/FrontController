<?php

/**
 * @todo: Everything
 */
class FunctionalTest extends PHPUnit_Extensions_Selenium2TestCase
{
    public function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl('http://localhost/github/Arrouter/');
    }

    public function testGetRootDisplaysGreeting()
    {
        $this->url('http://localhost/github/Arrouter/');
        $body = $this->byCssSelector('h1');
        $this->assertEquals('Hello, World!', $body->text());
    }
}
