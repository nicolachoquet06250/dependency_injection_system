<?php

	use mvc_router\confs\Conf;
	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/../autoload.php';

	Dependency::add_custom_dependency('\mvc_router\mvc\views\Translation', 'translation_view',
									  __DIR__.'/classes/views/Translation.php', '\mvc_router\mvc\View');

	Dependency::add_custom_dependency('\mvc_router\mvc\views\BasicView', 'my_basic_view',
									  __DIR__.'/classes/views/BasicView.php', '\mvc_router\mvc\View');

	Dependency::add_custom_dependency('\mvc_router\data\gesture\custom\managers\MyManager', 'my_manager',
								  __DIR__.'/classes/datas/managers/MyManager.php',
									  '\mvc_router\data\gesture\Manager');

	Dependency::add_custom_dependency('\mvc_router\data\gesture\custom\entities\MyManager', 'entity_my_manager',
								  __DIR__.'/classes/datas/entities/MyManager.php',
								  '\mvc_router\data\gesture\Manager');

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

	Conf::extend_confs(
		[
			'class' => [
				'old' => 'mvc_router\confs\Mysql',
				'new' => 'mvc_router\confs\custom\Mysql'
			],
			'name' => 'mysql',
			'file' => __DIR__.'/classes/confs/Mysql.php',
		]
	);
