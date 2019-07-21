<?php

	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/../autoload.php';

	Dependency::add_custom_dependency('\MyController', 'my_controller',
		__DIR__ . '/classes/controllers/MyController.php', '\mvc_router\mvc\Controller');
	Dependency::require_dependency_wrapper();

	spl_autoload_register([Dependency::class, 'autoload'], true);
