<?php
/**
 * Mandarin.php
 *
 * Definition of Mandarin class.
 *
 * PHP version 5.4
 * 
 * @author ifcanduela <ifcanduela@gmail.com>
 */

namespace ifcanduela\mandarin;

/**
 * Mandarin: A front controller.
 *
 * @author ifcanduela <ifcanduela@gmail.com>
 */
class Mandarin
{
    /**
     * Constants for URL pattern matching
     */
    const FUZZY_MATCH = false;
    const EXACT_MATCH = true;

    /**
     * Constants for the RESTful HTTP request methods
     */
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * Constants for URL sections
     */
    const URL_PROTOCOL = 'protocol';
    const URL_USERNAME = 'username';
    const URL_PASSWORD = 'password';
    const URL_HOST     = 'host';
    const URL_POSR     = 'port';
    const URL_PATH     = 'path';
    const URL_URI      = 'uri';

    /**
     * Constants for callbacks
     */
    const BEFORE_ROUTE  = 'beforeRoute';
    const AFTER_ROUTE   = 'afterRoute';
    const BEFORE_ACTION = 'beforeAction';
    const AFTER_ACTION  = 'afterAction';

    /**
     * Stores the routes.
     * @var array
     */
    protected $routes = array(
            self::METHOD_GET    => array(),
            self::METHOD_POST   => array(),
            self::METHOD_PUT    => array(),
            self::METHOD_DELETE => array(),
        );

    /**
     * URI path separator.
     * @var string
     */
    protected $uriSeparator = '/';

    /**
     * URL segments.
     * @var  array
     */
    protected $segments = array(
            /**
             * http:// or https://
             * @var string
             */
            self::URL_PROTOCOL => '',
            /**
             * for basic http authentication
             * @var string
             */
            self::URL_USERNAME => '',
            /**
             * for basic http authentication
             * @var string
             */
            self::URL_PASSWORD => '',
            /**
             * Host or domain name.
             * @var string
             */
            self::URL_HOST     => '',
            /**
             * Port
             * @var int
             */
            self::URL_HOST     => 80,
            /**
             * URL path after the domain.
             * @var string
             */
            self::URL_PATH     => '',
        );

    /**
     * URI request segments.
     * @var array
     */
    protected $uriSegments = array();

    /**
     * GET variable that contains the URI.
     * 
     * This is either established by an .htacces file or via the URLs
     * @var string
     */
    protected $uriVariableName = 'p';

    /**
     * Singleton instance of the framework.
     * @var Mandarin
     */
    protected static $instance = null;

    /**
     * 
     */
    protected $exitAfterRoutingFailure = true;

    /**
     * HTTP method of the current request.
     * @var string
     */
    protected $requestMethod;

    /**
     * HTTP Get data
     * @var array
     */
    public $get = array();

    /**
     * HTTP Post data
     * @var array
     */
    public $post = array();

    /**
     * HTTP Files data
     * @var array
     */
    public $files = array();

    /**
     * Callbacks for certain events.
     * @var array
     */
    protected $callbacks = array(
            /**
             * @param Mandarin   $app       Current instance of the application
             * @param string     $uri       The URI
             */
            'beforeRoute'  => null,

            /**
             * @param Mandarin   $app       Current instance of the application
             * @param string     $uri       The URI
             * @param string     $pattern   The regular expression pattern
             */
            'afterRoute'   => null,

            /**
             * @param Mandarin   $app       Current instance of the application 
             * @param array      $arguments List of arguments for the action
             */
            'beforeAction' => null,

            /**
             * @param Mandarin   $app       Current instance of the application
             * @param object     $result    Result of calling the action callback
             */
            'afterAction'  => null,
        );

    /**
     * Private: use Mandarin::app();
     */
    private function __construct()
    {
        $this->segments[self::URL_PROTOCOL] = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        // @todo: extract username
        // @todo: extract password
        $this->segments[self::URL_HOST]     = trim($_SERVER['SERVER_NAME'], '/') . '/';
        // @todo: extract port
        $this->segments[self::URL_PATH]     = ltrim(str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']), '/');
        $this->segments[self::URL_URI]      = '';

        $this->requestMethod = $_SERVER['REQUEST_METHOD'];

        $this->post = array_map("filter_var", $_POST);
        $this->files = $_FILES;
        $this->get = array_map("urldecode", $_GET);

        $this->getUriString();
    }

    /**
     * Fetches the singleton instance of the Mandarin application.
     *
     * @param bool $reset Set to true to reset all data
     * 
     * @return Mandarin Instance of the Mandarin application
     * @static
     */
    public static function app($reset = false)
    {
        if (is_null(self::$instance) || $reset === true) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Set the character that separates URI segments.
     * 
     * @param string $separator A single character
     * 
     * @return Mandarin The Mandarin application object 
     */
    public function setUriSeparator($separator)
    {
        if (!$separator || !is_string($separator)) {
            throw new \InvalidArgumentException("Function argument \$separator must be a string.");
        }

        $this->uriSeparator = $separator{0};

        return $this;
    }

    /**
     * Get the string that separates URI segments.
     * 
     * @return string The segment separation character
     */
    public function getUriSeparator()
    {
        return $this->uriSeparator;
    }

    /**
     * Set the variable name of the GET parameter that carries the route.
     * 
     * @param string $variableName Name of the variable
     * 
     * @return Mandarin The Mandarin object
     */
    public function setUriVariableName($variableName)
    {
        if (is_string($variableName) && preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $variableName)) {
            $this->uriVariableName = $variableName;
        } else {
            throw new \InvalidArgumentException("Function argument \$variableName must be a valid PHP variable name.");
        }

        return $this;
    }

    /**
     * Get the name of variable that carries the route.
     * 
     * @return string The variable name
     */
    public function getUriVariableName()
    {
        return $this->uriVariableName;
    }

    /**
     * Adds a route to the application.
     * 
     * @param string   $method     An request method
     * @param string   $route      URI pattern
     * @param callable $action     Action to take if the actual route matches the pattern
     * @param bool     $exactMatch Exact matching or Fuzzy matching
     * 
     * @return array               The route data
     */
    public function addRoute($method, $route, $action, $exactMatch = self::FUZZY_MATCH)
    {
        # Ensure the action is callable
        if (!is_callable($action)) {
            throw new \InvalidArgumentException("The \$action argument must be callable");
        }

        # Prepare the url
        $route = trim((string) $route, '/');

        # Prepare the pattern
        $pattern = preg_replace('~:([^/]+)~', '(?P<$1>[^\/]+)', $route);

        # Create the routes array for the request method
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = array();
        }

        $routeData = array(
                'route'    => $route,
                'pattern'  => $exactMatch === self::EXACT_MATCH ? "~^$pattern\$~" : "~$pattern~",
                'callable' => new \ReflectionFunction($action),
            );

        $this->routes[$method][] = $routeData;

        return $routeData;
    }

    /**
     * Adds a GET route to the application.
     * 
     * @param string   $route      URL route to match against
     * @param callable $callable   Action to take upon a positive match
     * @param boolean  $exactMatch Perform a strict comparison
     * 
     * @return array               The route data
     */
    public function get($route, $callable, $exactMatch = self::FUZZY_MATCH)
    {
        return $this->addRoute(self::METHOD_GET, $route, $callable, $exactMatch);
    }

    /**
     * Adds a POST route to the application.
     * 
     * @param string   $route      URL route to match against
     * @param callable $callable   Action to take upon a positive match
     * @param boolean  $exactMatch Perform a strict comparison
     * 
     * @return array               The route data
     */
    public function post($route, $callable, $exactMatch = self::FUZZY_MATCH)
    {
        return $this->addRoute(self::METHOD_POST, $route, $callable, $exactMatch);
    }

    /**
     * Adds a PUT route to the application.
     * 
     * @param string   $route      URL route to match against
     * @param callable $callable   Action to take upon a positive match
     * @param boolean  $exactMatch Perform a strict comparison
     * 
     * @return array               The route data
     */
    public function put($route, $callable, $exactMatch = self::FUZZY_MATCH)
    {
        return $this->addRoute(self::METHOD_PUT, $route, $callable, $exactMatch);
    }

    /**
     * Adds a DELETE route to the application.
     * 
     * @param string   $route      URL route to match against
     * @param callable $callable   Action to take upon a positive match
     * @param boolean  $exactMatch Perform a strict comparison
     * 
     * @return array               The route data
     */
    public function delete($route, $callable, $exactMatch = self::FUZZY_MATCH)
    {
        return $this->addRoute(self::METHOD_DELETE, $route, $callable, $exactMatch);
    }

    /**
     * Adds a route to all route lists.
     * 
     * @param string   $route      URL route to match against
     * @param callable $callable   Action to take upon a positive match
     * @param boolean  $exactMatch Perform a strict comparison
     * 
     * @return array               The route data
     */
    public function any($route, $callable, $exactMatch = self::FUZZY_MATCH)
    {
        $get    = $this->get($route, $callable, $exactMatch);
        $post   = $this->post($route, $callable, $exactMatch);
        $put    = $this->put($route, $callable, $exactMatch);
        $delete = $this->delete($route, $callable, $exactMatch);

        return compact('get', 'post', 'put', 'delete');
    }

    /**
     * Deletes configured routes.
     * 
     * @param string $method Method for which to remove routes (null for all methods)
     * 
     * @return null
     */
    public function clear($method = null)
    {
        if (!$method) {
            $this->routes = array();
        } else {
            $this->routes[$method] = array();
        }
    }

    /**
     * Get the requested URI.
     *
     * @param string $url The current request URI
     * 
     * @return string The URI
     */
    public function getUriString($url = null)
    {
        if ($url !== null) {
            $this->segments[self::URL_URI] = trim($url, $this->uriSeparator);
        } elseif (isset($this->get[$this->uriVariableName])) {
            $this->segments[self::URL_URI] = $this->get[$this->uriVariableName];
        } else {
            $this->segments[self::URL_URI] = '';
        }

        $this->uriSegments = explode($this->uriSeparator, trim($this->segments[self::URL_URI], $this->uriSeparator));

        return $this->segments[self::URL_URI];
    }

    /**
     * Executes the application.
     * 
     * @param string $url URL to route
     * 
     * @return mixed  Return nvalue of the callable
     */
    public function run($url = null)
    {
        # Prepare the url
        $url = $this->getUriString($url);

        # Set up the segments
        $segments = array_values(array_filter(explode('/', $url)));
        $this->segments += array_map('urldecode', $segments);

        # Trigger beforeRoute callback
        $this->invokeCallback(self::BEFORE_ROUTE, array($this));

        # Find a suitable route pattern
        $route = $this->findMatchingRoute($url);

        # If there is no applicable route pattern, display a 404
        if ($route === null) {
            return (object) array('code' => 404);
        }

        # Trigger afterRoute callback
        $this->invokeCallback(self::AFTER_ROUTE, array($this, $route));

        # Build the argument list
        $arguments = $this->getClosureArguments($route, $url);

        # Trigger beforeAction callback
        $this->invokeCallback(self::BEFORE_ACTION, array($this, $arguments));

        # Call the action closure with the arguments
        $return = $route['callable']->invokeArgs($arguments);

        # Trigger afterAction callback
        $this->invokeCallback(self::AFTER_ACTION, array($this, $return));

        # Return value of the action
        return (object) array('code' => 200, 'return_value' => $return);
    }

    /**
     * Retrieves the first matching route.
     * 
     * @param string $uri The URI to match against
     * 
     * @return array       
     */
    protected function findMatchingRoute($uri)
    {
        if (isset($this->routes[$this->requestMethod]) && is_array($this->routes[$this->requestMethod])) {
            foreach ($this->routes[$this->requestMethod] as $k => $r) {
                if (preg_match($r['pattern'], $uri)) {
                    return $r;
                }
            }
        }
        
        return null;
    }

    /**
     * Gets the arguments of the selected action closure.
     * 
     * @param array  $route A pre-configured route
     * @param string $uri   Current requested URI
     * 
     * @return array Arguments for the callback
     */
    protected function getClosureArguments($route, $uri)
    {
        # Get the route tags
        if (!preg_match($route['pattern'], $uri, $matches)) {
            throw new \RuntimeException("Something went wrong.");
        }

        $arguments = array();

        # Walk through the parameters of the closure
        foreach ($route['callable']->getParameters() as $parameter) {
            # Throw an exception if there is a URL tag without matching closure parameter
            if (!isset($matches[$parameter->name])) {
                throw new \RuntimeException("Callable arguments and tags don't match!");
            }
            # Add the matching argument in the proper order to the array
            $arguments[] = urldecode($matches[$parameter->name]);
        }

        return $arguments;
    }

    /**
     * Retrieves a URL segment.
     *
     * Special segments: URL_PROTOCOL, URL_HOST, URL_PATH
     * 
     * @param int $segment Segment key or number 
     * 
     * @return string The segment or false if it does not exist
     */
    public function segment($segment = 1)
    {
        if (is_string($segment) && isset($this->segments[$segment])) {
            return $this->segments[$segment];
        } elseif (isset($this->segments[$segment - 1])) {
            return $this->segments[$segment - 1];
        } else {
            return null;
        }
    }

    /**
     * Retrieves the canonical URL of the application.
     *
     * @param string $uri Additional URI segments
     * 
     * @return string The URL, ending with a forward slash
     */
    public function base($uri = '')
    {
        return $this->segments['protocol'] . $this->segments[ 'host'] . $this->segments['path'] . rtrim(ltrim($uri, '/'), '/');
    }

    /**
     * Invokes one of the callbacks.
     *  
     * @param string $type      Type of callbacks
     * @param array  $arguments Arguments passed to the callback
     * 
     * @return mixed Return value of the callbak
     */
    protected function invokeCallback($type, array $arguments = null)
    {
        # Call the afterAction callback if exists
        if (is_callable($this->callbacks[$type])) {
            return call_user_func_array($this->callbacks[$type], (array) $arguments);
        } else {
            return null;
        }
    }

    /**
     * Assigns a callback to run before routing.
     * 
     * @param callable $callback Action to take before routing
     *
     * @return null
     */
    public function beforeRoute(/*callable*/ $callback)
    {
        $this->callbacks[self::BEFORE_ROUTE] = $callback;
    }

    /**
     * Assigns a callback to run after routing.
     * 
     * @param callable $callback Action to take after routing
     *
     * @return null
     */
    public function afterRoute(/*callable*/ $callback)
    {
        $this->callbacks[self::AFTER_ROUTE] = $callback;
    }

    /**
     * Assigns a callback to run before running the action.
     * 
     * @param callable $callback Action to take before routing
     *
     * @return null
     */
    public function beforeAction(/*callable*/ $callback)
    {
        $this->callbacks[self::BEFORE_ACTION] = $callback;
    }

    /**
     * Assigns a callback to run after running the action.
     * 
     * @param callable $callback Action to take after the action
     *
     * @return null
     */
    public function afterAction(/*callable*/ $callback)
    {
        $this->callbacks[self::AFTER_ACTION] = $callback;
    }

    /**
     * Sets whether the run method should throw an exception if no
     * valid route is found.
     * 
     * @param boolean $value True to throw Exception
     * 
     * @return boolean Current setting
     */
    public function exitAfterRoutingFailure($value = null)
    {
        if (is_null($value)) {
            return $this->exitAfterRoutingFailure;
        } else {
            $this->exitAfterRoutingFailure = (bool) $value;
        }
    }
}
