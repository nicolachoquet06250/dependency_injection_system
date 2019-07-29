<?php

	use mvc_router\confs\Conf;
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
		],
		[
			'class' => '\mvc_router\mvc\backoffice\Translations',
			'name' => 'backoffice_translations',
			'file' => __DIR__.'/classes/controllers/backoffice/Translations.php',
		]
	);

	Conf::extend_conf('mvc_router\confs\Mysql', 'mvc_router\confs\custom\Mysql', [
		'name' => 'mysql',
		'type' => Conf::NONE,
		'file' => __DIR__.'/classes/confs/Mysql.php',
	]);
