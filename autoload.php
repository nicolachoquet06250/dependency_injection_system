<?php

	use mvc_router\confs\Conf;
	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/classes/Dependency.php';
	require_once __DIR__.'/classes/Conf.php';

	register_shutdown_function([Dependency::class, 'fatal_handler']);
	spl_autoload_register([Dependency::class, 'autoload'], true);

	Dependency::load_base_dependencies();
	Dependency::require_dependency_wrapper();
	Conf::require_conf_wrapper();
