<?php

use mvc_router\dependencies\Dependency;
use mvc_router\router\Router;

$router = Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_router();

$router->route('/', 'my_controller');
$router->route('/mon/example/', 'my_controller', Router::DEFAULT_ROUTE_METHOD);
$router->route('/mon/example/2', 'my_controller', 'test');
$router->route('\/([a-zA-Z]+)\/([0-9]+)', 'my_controller', 'test2', Router::REGEX);

$router->inspect_controllers();
