# Front Controller

Trying out the front controller/microframework pattern in PHP.

Don't use this.

## Usage

    <?php

    require 'mandarin/Mandarin.php';
    $app = \mandarin\Mandarin::app();

    $app->get('/form', function() {
            ?>
                <form action="greeting" method="post">
                    <input type="text" name="location" value="World">
                    <input type="submit">
                </form>
            <?php
        }, $app::STRICT_MATCHING);

    $app->post('/greeting', function() {
            echo "Hello, {$this->post['location']}!";
        });

    $app->get('/', function() {
            echo "Nothing here, try /form.";
        });

    $result = $app->run();

    if ($result->code === 404) {
        echo "Not found";
    }

You can define a callback and use it like this:

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

