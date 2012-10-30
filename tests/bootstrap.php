<?php 

require '../arrouter.php';

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/github/Arrouter/tests/bootstrap.php';
$_SERVER['REQUEST_URI'] = '/';

function setRequestUri($uri)
{
    $_SERVER['REQUEST_URI'] = $uri;
}

function setRequestMethod($method)
{
    $_SERVER['REQUEST_METHOD'] = $method;
}
