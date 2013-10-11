<?php

require 'mandarin/Mandarin.php';

use ifcanduela\mandarin\Mandarin;

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/dummy/bootstrap.php';
$_SERVER['REQUEST_URI'] = '/';

function setRequestUri($uri)
{
    $_SERVER['REQUEST_URI'] = $uri;
}

function setRequestMethod($method)
{
    $_SERVER['REQUEST_METHOD'] = $method;
}

$app = Mandarin::app()->autoRun();

$app->get('/:name', function($name) use ($app) {
    echo "<h1>Hello, $name!</h1>";
    var_dump($app);
});

$app->get('/', function() use($app) {
    echo "<h1>Hello, World!</h1>";
    var_dump($app);
});
