<?php

use mvc_router\dependencies\Dependency;

$router = Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_router();

$router->root_route('my_controller');

$router->inspect_controllers();
