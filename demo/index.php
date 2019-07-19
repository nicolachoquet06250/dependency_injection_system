<?php

use mvc_router\dependencies\Dependency;

try {
	require_once __DIR__.'/../autoload.php';
	require_once __DIR__.'/update_dependencies.php';
	require_once __DIR__.'/htaccess.php';

	$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();

	echo $dw->get_router()->execute($_SERVER['REQUEST_URI']);

	$controller = $dw->get_my_controller();
}
catch (Exception $e) {
	exit($e->getMessage());
}
catch (Error $e) {
	exit($e->getMessage());
}
