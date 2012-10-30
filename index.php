<?php

require 'arrouter.php';

$app = Arrouter::app();

$app->get('/:name', function($name) use ($app) {
    echo "<h1>Hello, $name!</h1>";
    var_dump($app);
});

$app->get('/', function() use($app) {
    echo "<h1>Hello, World!</h1>";
    var_dump($app);
});

$app->run();