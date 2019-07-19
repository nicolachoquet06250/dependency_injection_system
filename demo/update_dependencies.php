<?php

	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/../autoload.php';

	Dependency::add_custom_dependency('\MyController', 'my_controller',
									  __DIR__.'/classes/MyController.php', '\mvc_router\mvc\Controller');
	Dependency::add_custom_dependency('\my_app\services\Service', 'my_service', __DIR__.'/classes/Service.php');
	Dependency::require_dependency_wrapper();

	function __autoload($class) {
		Dependency::autoload($class);
	}