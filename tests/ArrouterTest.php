<?php 

class ArrouterTest extends PHPUnit_Framework_TestCase
{
    protected $app = null;

    public function testAppInitialization()
    {
        $app = Arrouter::app();

        $this->assertEquals('http://localhost/github/Arrouter/tests/', $app->base());
            
        $this->assertEquals(array(), $app->get);
        $this->assertEquals(array(), $app->post);
        $this->assertEquals(array(), $app->files);

        $this->assertEquals('/', $app->getUriSeparator());
        $this->assertEquals('p', $app->getUriVariableName());
    }

    public function testBaseMethodReturnsBaseUrlAndExtraSegments()
    {
        $app = Arrouter::app();
        
        $this->assertEquals('http://localhost/github/Arrouter/tests/', $app->base());
        $this->assertEquals('http://localhost/github/Arrouter/tests/controller/action', $app->base('controller/action'));
        $this->assertEquals('http://localhost/github/Arrouter/tests/requested/path', $app->base('/requested/path/'));
    }

    public function testSegmentMethodReturnsEnvironmentValues()
    {
        $app = Arrouter::app();

        $this->assertEquals('http://', $app->segment($app::URL_PROTOCOL));
        $this->assertEquals('localhost/', $app->segment($app::URL_HOST));
        $this->assertEquals('github/Arrouter/tests/', $app->segment($app::URL_PATH));

        ob_start();
        $app->run('/');
        ob_end_clean();

        $this->assertEquals('http://', $app->segment($app::URL_PROTOCOL));
        $this->assertEquals('localhost/', $app->segment($app::URL_HOST));
        $this->assertEquals('github/Arrouter/tests/', $app->segment($app::URL_PATH));
        $this->assertEquals('', $app->segment($app::URL_URI));
    }

    public function testSegmentMethodReturnsRequestValues()
    {
        $app = Arrouter::app();

        ob_start();
        $app->run('/');
        ob_end_clean();

        $this->assertEquals('http://', $app->segment($app::URL_PROTOCOL));
        $this->assertEquals('localhost/', $app->segment($app::URL_HOST));
        $this->assertEquals('github/Arrouter/tests/', $app->segment($app::URL_PATH));
        $this->assertEquals('', $app->segment($app::URL_URI));

        ob_start();
        $app->run('/alpha/beta/gamma');
        ob_end_clean();

        $this->assertEquals('http://', $app->segment($app::URL_PROTOCOL));
        $this->assertEquals('localhost/', $app->segment($app::URL_HOST));
        $this->assertEquals('github/Arrouter/tests/', $app->segment($app::URL_PATH));
        $this->assertEquals('alpha/beta/gamma', $app->segment($app::URL_URI));
        $this->assertEquals('alpha', $app->segment(1));
        $this->assertEquals('beta', $app->segment(2));
        $this->assertEquals('gamma', $app->segment(3));
        $this->assertNull($app->segment(0));
        $this->assertNull($app->segment(4));
    }

    public function testExistAfterFailureReturnsCorrectvalue()
    {
        $app = Arrouter::app();

        $this->assertTrue($app->exitAfterRoutingFailure());

        $app->exitAfterRoutingFailure(false);
        $this->assertFalse($app->exitAfterRoutingFailure());
        
        $app->exitAfterRoutingFailure(true);
        $this->assertTrue($app->exitAfterRoutingFailure());
    }

    public function testValidValuesForUriSeparator()
    {
        $app = Arrouter::app();

        $this->assertEquals('/', $app->getUriSeparator());

        $app->setUriSeparator(' ');
        $this->assertEquals(' ', $app->getUriSeparator());

        $app->setUriSeparator('abc=');
        $this->assertEquals('a', $app->getUriSeparator());

        $app->setUriSeparator('a b c');
        $this->assertEquals('a', $app->getUriSeparator());

        $app->setUriSeparator('a,b');
        $this->assertEquals('a', $app->getUriSeparator());

        $app->setUriSeparator('a-b-c');
        $this->assertEquals('a', $app->getUriSeparator());

        $app->setUriSeparator('=');
        $this->assertEquals('=', $app->getUriSeparator());

        $app->setUriSeparator('1');
        $this->assertEquals('1', $app->getUriSeparator());

        $app->setUriSeparator('_)(');
        $this->assertEquals('_', $app->getUriSeparator());
    }

    public function testInvalidValuesForUriSeparator()
    {
        $app = Arrouter::app();

        $app->setUriSeparator('/');
        $this->assertEquals('/', $app->getUriSeparator());

        try {
            $app->setUriSeparator(1234);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $app->getUriSeparator());
        }

        try {
            $app->setUriSeparator(array());
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $app->getUriSeparator());
        }

        try {
            $app->setUriSeparator(new StdClass);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $app->getUriSeparator());
        }

        try {
            $app->setUriSeparator('');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $app->getUriSeparator());
        }

        try {
            $app->setUriSeparator(false);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $app->getUriSeparator());
        }
    }

    public function testValidValuesForUriVariableName($value='')
    {
        $app = Arrouter::app();

        $app->setUriVariableName('p');
        $this->assertEquals('p', $app->getUriVariableName());
        
        $app->setUriVariableName('route');
        $this->assertEquals('route', $app->getUriVariableName());
        
        $app->setUriVariableName('_');
        $this->assertEquals('_', $app->getUriVariableName());
        
        $app->setUriVariableName('_12');
        $this->assertEquals('_12', $app->getUriVariableName());
        
        $app->setUriVariableName('_a');
        $this->assertEquals('_a', $app->getUriVariableName());
        
        $app->setUriVariableName('abc123');
        $this->assertEquals('abc123', $app->getUriVariableName());
    }

    public function testInvalidValuesForUriVariableName()
    {
        $app = Arrouter::app();
        $app->setUriVariableName('p');

        try {
            $app->setUriVariableName(1234);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }

        try {
            $app->setUriVariableName(array());
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }

        try {
            $app->setUriVariableName(new StdClass);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }

        try {
            $app->setUriVariableName('1234');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }

        try {
            $app->setUriVariableName('a-b-c');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }

        try {
            $app->setUriVariableName('var "var"');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }

        try {
            $app->setUriVariableName('<var>');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }

        try {
            $app->setUriVariableName('$var');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $app->getUriVariableName());
        }
    }

    public function testGetSiteRootRouteWithFuzzyMatching()
    {
        $app = Arrouter::app();

        $app->get('/', function()
        {
            echo 'Hello';
        });

        ob_start();
        $app->run('');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $app->run('/');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $app->run('/a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);
        
        ob_start();
        $app->run('a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);
    }

    public function testGetSiteRootRouteWithExactMatching()
    {
        $app = Arrouter::app();

        $app->clear();
        $app->exitAfterRoutingFailure(false);

        $app->get('/', function()
        {
            echo 'Hello';
        }, Arrouter::EXACT_MATCH);

        ob_start();
        $app->run('');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $app->run('/');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $app->run('/a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertNotEquals('Hello', $result);
        
        ob_start();
        $app->run('a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertNotEquals('Hello', $result);
    }

    public function testOnlyCallablesAreAllowed()
    {
        $app = Arrouter::app();

        try {
            $app->get('/', "ucwords");
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }

        try {
            $app->get('/', new stdClass);
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testBeforeRouteAndAfterRouteCallbacksAreCalled()
    {
        $app = Arrouter::app();

        $app->clear('GET');

        $app->get('/', function() { echo "Action"; });

        ob_start();
        $app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('Action', $output);

        $app->beforeRoute(function() {
            echo "BeforeRoute";
        });

        ob_start();
        $app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeRouteAction', $output);

        $app->afterRoute(function() {
            echo "AfterRoute";
        });

        ob_start();
        $app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeRouteAfterRouteAction', $output);

        $app->beforeRoute(null);
        $app->afterRoute(null);
    }

    public function testBeforeActionAndAfterActionCallbacksAreCalled()
    {
        $app = Arrouter::app();

        $app->clear('GET');

        $app->get('/', function() { echo "Action"; });

        ob_start();
        $app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('Action', $output);

        $app->beforeAction(function() {
            echo "BeforeAction";
        });

        ob_start();
        $app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeActionAction', $output);

        $app->afterAction(function() {
            echo "AfterAction";
        });

        ob_start();
        $app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeActionActionAfterAction', $output);

        $app->beforeRoute(null);
        $app->afterRoute(null);
        $app->beforeAction(null);
        $app->afterAction(null);
    }

    public function testAssignmentOfValuesToRouteTags()
    {
        $app = Arrouter::app();
        $app->clear();
        $app->get('test/:word/and/:number', function ($word, $number)
            {
                echo "$word $number";
            });

        ob_start();
        $result = $app->run('test/Catch/and/22');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(200, $result->code);
        $this->assertEquals('Catch 22', $output);
    }

    public function testUriStringCreationFromGetVariable()
    {
        $_GET['p'] = 'alpha/beta/gamma';

        $app = Arrouter::app(true);

        $this->assertEquals('alpha/beta/gamma', $app->segment($app::URL_URI));
    }

    public function testForwardSlashDoesNotMatter()
    {
        $app = Arrouter::app(true);

        $app->get('/slash', function() {
            echo "ForwardSlashesDoNotMatter";
        });

        ob_start();
        $result = $app->run('/slash');
        $forwardSlash = ob_get_contents();
        ob_end_clean();

        ob_start();
        $result = $app->run('slash');
        $noForwardSlash = ob_get_contents();
        ob_end_clean();

        $app->clear();

        $app->get('slash', function() {
            echo "ForwardSlashesDoNotMatter";
        });

        ob_start();
        $result = $app->run('/slash');
        $forwardSlashInUri = ob_get_contents();
        ob_end_clean();

        ob_start();
        $result = $app->run('slash');
        $noForwardSlashInUri = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($forwardSlash, $noForwardSlash);
        $this->assertEquals($forwardSlashInUri, $noForwardSlashInUri);
        $this->assertEquals($forwardSlash, $forwardSlashInUri);
        $this->assertEquals($noForwardSlash, $noForwardSlashInUri);
    }
    
    /**
     * @expectedException RuntimeException
     */
    public function testExceptionIsThrownWhenCallableParametersAreWrong()
    {
        $app = Arrouter::app(true);

        $app->get('/:first/:second', function($first, $notSecond) {
            echo "This should not happen.";
        });

        $result = $app->run('/alpha/beta');
    }

    public function testAddingPostPutAndDeleteRoutes()
    {
        $app = Arrouter::app();

        $app->post('form', function() {

        });

        $app->put('form', function() {
            
        });

        $app->delete('form', function() {
            
        });
    }
}
