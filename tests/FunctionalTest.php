<?php

/**
 * @todo: Everything
 */
class FunctionalTest extends PHPUnit_Extensions_Selenium2TestCase
{
    public function setUp()
    {
        $this->setBrowser('chrome');
        $this->setBrowserUrl('http://localhost/github/Arrouter/');
    }

    public function testGetRootDisplaysGreeting()
    {
        $app = Arrouter::app();

        $this->url('http://www.example.com/');
        $this->assertEquals('Example WWW Page', $this->title());
    }
}