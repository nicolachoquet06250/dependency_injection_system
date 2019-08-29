<?php

	use mvc_router\confs\Conf;
	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/classes/exceptions/http_errors/Exception400.php';
	require_once __DIR__.'/classes/exceptions/http_errors/Exception401.php';
	require_once __DIR__.'/classes/exceptions/http_errors/Exception404.php';
	require_once __DIR__.'/classes/exceptions/http_errors/Exception500.php';

	require_once __DIR__.'/classes/Dependency.php';
	require_once __DIR__.'/classes/Conf.php';

	if(is_dir(__DIR__.'/vendor') && is_file(__DIR__.'/vendor/autoload.php')) {
		require_once __DIR__.'/vendor/autoload.php';
	}

	register_shutdown_function([Dependency::class, 'fatal_handler']);
	spl_autoload_register([Dependency::class, 'autoload'], true);

	Dependency::load_base_dependencies();
	Dependency::require_dependency_wrapper();
	Conf::require_conf_wrapper();

	$triggers = Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_triggers();
	if($triggers) {
		$triggers->initialize();
	}
