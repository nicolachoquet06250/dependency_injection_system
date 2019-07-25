<?php

	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/../autoload.php';

	Dependency::add_custom_controllers(
		[
			'class' => '\MyController',
			'name' => 'my_controller',
			'file' => __DIR__ . '/classes/controllers/MyController.php',
		],
		[
			'class' => '\mvc_router\mvc\api\ControllerAPI1',
			'name' => 'api_user',
			'file' => __DIR__.'/classes/controllers/api/ControllerAPI1.php',
		]
	);
