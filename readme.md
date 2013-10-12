# Front Controller

Trying out the front controller/microframework pattern in PHP.

Don't use this.

## Usage

    <?php

    require 'mandarin/Mandarin.php';
    use \ifcanduela\mandarin\Mandarin;

    $app = Mandarin::app();

    $app->get('/form/:section', function($section) {
            require $section . '.html';
        }, Mandarin::EXACT_MATCH);

    $app->post('/greeting', function() {
            echo "Hello, {$this->post['location']}!";
        });

    # two routes with the same handler
    $app->get(array('/', '/home'), function() {
            echo "Nothing here, try /form.";
        });

    $result = $app->run();

    if ($result->code === 404) {
        echo "Not found";
    }

### Actions

You can define a callback and attach it to a route like this:

    <?php

    require 'mandarin/Mandarin.php';
    $app = \mandarin\Mandarin::app();

    function myCallback($name)
    {
        return array("name" => ucfirst($name));
    }

    $app->get('/:name', 'myCallback');

    $result = $app->run();

    echo "Name = {$result->return_value['name']}";

Or like this:

    <?php

    class MyCallback
    {
        public function run($name)
        {
            return array("name" => ucfirst($name));
        }
    }

    $app->get('/:name', array('MyCallback', 'run'));

### Callback Hooks

There are four hooks for callbacks:

- `beforeRoute`
- `afterRoute`
- `beforeAction`
- `afterAction`

There are two ways to attach the callbacks:

    $app->beforeAction(function() { ... });

    $app->on('AfterRoute', function() { ... });

Using the later method you can attach a `404 Not Found` handler, too:

    $app->on('404', 'callable_handler');

## Settings

### AutoRun

By calling `autoRun()`, the `run()` method will be called automatically as the scripts ends.

    $app = Mandarin::app()->autoRun();

### Matching type

The default route-matching behavior is *fuzzy*, meaning that it will only match any part of a route. You
can change that in a route-by-route basis or by using the `setMatchgingtype()` method:

    $app->setMatchingType(Mandarin::EXACT_MATCHING);

### URL data

By default the class attempts to get the forward slash-delimited segments from a *GET* variable 
called `p`. You can change this with the following two calls:

    $app->setUriVariableName('path');
    $app->setUriSeparator('|');
