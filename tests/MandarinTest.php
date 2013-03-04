<?php 

require __DIR__ . '/../mandarin/Mandarin.php';

class MandarinTest extends PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = \mandarin\Mandarin::app(true);
    }

    public function tearDown()
    {
        $this->app->clear();
    }

    public function testAppInitialization()
    {
        $this->assertEquals('http://localhost/dummy/', $this->app->base());
            
        $this->assertEquals(array(), $this->app->get);
        $this->assertEquals(array(), $this->app->post);
        $this->assertEquals(array(), $this->app->files);

        $this->assertEquals('/', $this->app->getUriSeparator());
        $this->assertEquals('p', $this->app->getUriVariableName());
    }

    public function testBaseMethodReturnsBaseUrlAndExtraSegments()
    {
        $this->app = \mandarin\Mandarin::app();
        
        $this->assertEquals('http://localhost/dummy/', $this->app->base());
        $this->assertEquals('http://localhost/dummy/controller/action', $this->app->base('controller/action'));
        $this->assertEquals('http://localhost/dummy/requested/path', $this->app->base('/requested/path/'));
    }

    public function testSegmentMethodReturnsEnvironmentValues()
    {
        $this->assertEquals('http://', $this->app->segment(\mandarin\Mandarin::URL_PROTOCOL));
        $this->assertEquals('localhost/', $this->app->segment(\mandarin\Mandarin::URL_HOST));
        $this->assertEquals('dummy/', $this->app->segment(\mandarin\Mandarin::URL_PATH));

        ob_start();
        $this->app->run('/');
        ob_end_clean();

        $this->assertEquals('http://', $this->app->segment(\mandarin\Mandarin::URL_PROTOCOL));
        $this->assertEquals('localhost/', $this->app->segment(\mandarin\Mandarin::URL_HOST));
        $this->assertEquals('dummy/', $this->app->segment(\mandarin\Mandarin::URL_PATH));
        $this->assertEquals('', $this->app->segment(\mandarin\Mandarin::URL_URI));
    }

    public function testSegmentMethodReturnsRequestValues()
    {
        ob_start();
        $this->app->run('/');
        ob_end_clean();

        $this->assertEquals('http://', $this->app->segment(\mandarin\Mandarin::URL_PROTOCOL));
        $this->assertEquals('localhost/', $this->app->segment(\mandarin\Mandarin::URL_HOST));
        $this->assertEquals('dummy/', $this->app->segment(\mandarin\Mandarin::URL_PATH));
        $this->assertEquals('', $this->app->segment(\mandarin\Mandarin::URL_URI));

        ob_start();
        $this->app->run('/alpha/beta/gamma');
        ob_end_clean();

        $this->assertEquals('http://', $this->app->segment(\mandarin\Mandarin::URL_PROTOCOL));
        $this->assertEquals('localhost/', $this->app->segment(\mandarin\Mandarin::URL_HOST));
        $this->assertEquals('dummy/', $this->app->segment(\mandarin\Mandarin::URL_PATH));
        $this->assertEquals('alpha/beta/gamma', $this->app->segment(\mandarin\Mandarin::URL_URI));
        $this->assertEquals('alpha', $this->app->segment(1));
        $this->assertEquals('beta', $this->app->segment(2));
        $this->assertEquals('gamma', $this->app->segment(3));
        $this->assertNull($this->app->segment(0));
        $this->assertNull($this->app->segment(4));
    }

    public function testExistAfterFailureReturnsCorrectvalue()
    {
        $this->assertTrue($this->app->exitAfterRoutingFailure());

        $this->app->exitAfterRoutingFailure(false);
        $this->assertFalse($this->app->exitAfterRoutingFailure());
        
        $this->app->exitAfterRoutingFailure(true);
        $this->assertTrue($this->app->exitAfterRoutingFailure());
    }

    public function testValidValuesForUriSeparator()
    {
        $this->assertEquals('/', $this->app->getUriSeparator());

        $this->app->setUriSeparator(' ');
        $this->assertEquals(' ', $this->app->getUriSeparator());

        $this->app->setUriSeparator('abc=');
        $this->assertEquals('a', $this->app->getUriSeparator());

        $this->app->setUriSeparator('a b c');
        $this->assertEquals('a', $this->app->getUriSeparator());

        $this->app->setUriSeparator('a,b');
        $this->assertEquals('a', $this->app->getUriSeparator());

        $this->app->setUriSeparator('a-b-c');
        $this->assertEquals('a', $this->app->getUriSeparator());

        $this->app->setUriSeparator('=');
        $this->assertEquals('=', $this->app->getUriSeparator());

        $this->app->setUriSeparator('1');
        $this->assertEquals('1', $this->app->getUriSeparator());

        $this->app->setUriSeparator('_)(');
        $this->assertEquals('_', $this->app->getUriSeparator());
    }

    public function testInvalidValuesForUriSeparator()
    {
        $this->app->setUriSeparator('/');
        $this->assertEquals('/', $this->app->getUriSeparator());

        try {
            $this->app->setUriSeparator(1234);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $this->app->getUriSeparator());
        }

        try {
            $this->app->setUriSeparator(array());
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $this->app->getUriSeparator());
        }

        try {
            $this->app->setUriSeparator(new StdClass);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $this->app->getUriSeparator());
        }

        try {
            $this->app->setUriSeparator('');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $this->app->getUriSeparator());
        }

        try {
            $this->app->setUriSeparator(false);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('/', $this->app->getUriSeparator());
        }
    }

    public function testValidValuesForUriVariableName($value='')
    {
        $this->app->setUriVariableName('p');
        $this->assertEquals('p', $this->app->getUriVariableName());
        
        $this->app->setUriVariableName('route');
        $this->assertEquals('route', $this->app->getUriVariableName());
        
        $this->app->setUriVariableName('_');
        $this->assertEquals('_', $this->app->getUriVariableName());
        
        $this->app->setUriVariableName('_12');
        $this->assertEquals('_12', $this->app->getUriVariableName());
        
        $this->app->setUriVariableName('_a');
        $this->assertEquals('_a', $this->app->getUriVariableName());
        
        $this->app->setUriVariableName('abc123');
        $this->assertEquals('abc123', $this->app->getUriVariableName());
    }

    public function testInvalidValuesForUriVariableName()
    {
        $this->app->setUriVariableName('p');

        try {
            $this->app->setUriVariableName(1234);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }

        try {
            $this->app->setUriVariableName(array());
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }

        try {
            $this->app->setUriVariableName(new StdClass);
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }

        try {
            $this->app->setUriVariableName('1234');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }

        try {
            $this->app->setUriVariableName('a-b-c');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }

        try {
            $this->app->setUriVariableName('var "var"');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }

        try {
            $this->app->setUriVariableName('<var>');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }

        try {
            $this->app->setUriVariableName('$var');
        } catch (InvalidArgumentException $expected) {
            $this->assertEquals('p', $this->app->getUriVariableName());
        }
    }

    public function testGetSiteRootRouteWithFuzzyMatching()
    {
        $this->app->get('/', function()
        {
            echo 'Hello';
        });

        ob_start();
        $this->app->run('');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $this->app->run('/');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $this->app->run('/a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);
        
        ob_start();
        $this->app->run('a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);
    }

    public function testGetSiteRootRouteWithExactMatching()
    {
        $this->app->clear();
        $this->app->exitAfterRoutingFailure(false);

        $this->app->get('/', function()
        {
            echo 'Hello';
        }, \mandarin\Mandarin::EXACT_MATCH);

        ob_start();
        $this->app->run('');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $this->app->run('/');
        $result = ob_get_contents();
        ob_clean();

        $this->assertEquals('Hello', $result);

        ob_start();
        $this->app->run('/a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertNotEquals('Hello', $result);
        
        ob_start();
        $this->app->run('a');
        $result = ob_get_contents();
        ob_clean();

        $this->assertNotEquals('Hello', $result);
    }

    public function testOnlyCallablesAreAllowed()
    {
        try {
            $this->app->get('/', "ucwords");
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }

        try {
            $this->app->get('/', new stdClass);
        } catch (InvalidArgumentException $e) {
            $this->assertInstanceOf('InvalidArgumentException', $e);
        }
    }

    public function testBeforeRouteAndAfterRouteCallbacksAreCalled()
    {
        $this->app->clear('GET');

        $this->app->get('/', function() { echo "Action"; });

        ob_start();
        $this->app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('Action', $output);

        $this->app->beforeRoute(function() {
            echo "BeforeRoute";
        });

        ob_start();
        $this->app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeRouteAction', $output);

        $this->app->afterRoute(function() {
            echo "AfterRoute";
        });

        ob_start();
        $this->app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeRouteAfterRouteAction', $output);

        $this->app->beforeRoute(null);
        $this->app->afterRoute(null);
    }

    public function testBeforeActionAndAfterActionCallbacksAreCalled()
    {
        $this->app->clear('GET');

        $this->app->get('/', function() { echo "Action"; });

        ob_start();
        $this->app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('Action', $output);

        $this->app->beforeAction(function() {
            echo "BeforeAction";
        });

        ob_start();
        $this->app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeActionAction', $output);

        $this->app->afterAction(function() {
            echo "AfterAction";
        });

        ob_start();
        $this->app->run('/');
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals('BeforeActionActionAfterAction', $output);

        $this->app->beforeRoute(null);
        $this->app->afterRoute(null);
        $this->app->beforeAction(null);
        $this->app->afterAction(null);
    }

    public function testAssignmentOfValuesToRouteTags()
    {
        $this->app->get('test/:word/and/:number', function ($word, $number)
            {
                echo "$word $number";
            });

        ob_start();
        $result = $this->app->run('test/Catch/and/22');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(200, $result->code);
        $this->assertEquals('Catch 22', $output);
    }

    public function testUriStringCreationFromGetVariable()
    {
        $_GET['p'] = 'alpha/beta/gamma';
        $this->app = \mandarin\Mandarin::app(true);

        $this->assertEquals('alpha/beta/gamma', $this->app->segment(\mandarin\Mandarin::URL_URI));
    }

    public function testForwardSlashDoesNotMatter()
    {
        $this->app->get('/slash', function() {
            echo "ForwardSlashesDoNotMatter";
        });

        ob_start();
        $result = $this->app->run('/slash');
        $forwardSlash = ob_get_contents();
        ob_end_clean();

        ob_start();
        $result = $this->app->run('slash');
        $noForwardSlash = ob_get_contents();
        ob_end_clean();

        $this->app->clear();

        $this->app->get('slash', function() {
            echo "ForwardSlashesDoNotMatter";
        });

        ob_start();
        $result = $this->app->run('/slash');
        $forwardSlashInUri = ob_get_contents();
        ob_end_clean();

        ob_start();
        $result = $this->app->run('slash');
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
        $this->app->get('/:first/:second', function($first, $notSecond) {
            echo "This should not happen.";
        });

        $result = $this->app->run('/alpha/beta');
    }

    public function testAddingPostPutAndDeleteRoutes()
    {
        $this->app->post('form', function() {

        });

        $this->app->put('form', function() {
            
        });

        $this->app->delete('form', function() {
            
        });
    }
}

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/dummy/dummy_script.php';
$_SERVER['REQUEST_URI'] = '/';

function setRequestUri($uri)
{
    $_SERVER['REQUEST_URI'] = $uri;
}

function setRequestMethod($method)
{
    $_SERVER['REQUEST_METHOD'] = $method;
}
