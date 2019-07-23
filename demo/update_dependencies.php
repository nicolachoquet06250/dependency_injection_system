<?php

	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/../autoload.php';

	Dependency::add_custom_controller('\MyController', 'my_controller', __DIR__ . '/classes/controllers/MyController.php');
	Dependency::add_custom_controller('\mvc_router\mvc\api\ControllerAPI1', 'api_user', __DIR__.'/classes/controllers/api/ControllerAPI1.php');
	Dependency::add_custom_dependency('\mvc_router\services\Route', 'service_routes', __DIR__.'/classes/services/Route.php', '\mvc_router\Service');
	Dependency::extend_dependency('mvc_router\services\Toto', 'mvc_router\services\custom\Toto', [
		'name' => 'service_toto',
		'file' => __DIR__.'/classes/services/Toto.php',
		'type' => Dependency::NONE,

	]);
	Dependency::require_dependency_wrapper();

	spl_autoload_register([Dependency::class, 'autoload'], true);
