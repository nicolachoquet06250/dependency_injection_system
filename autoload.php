<?php

	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/classes/Dependency.php';

	Dependency::load_base_dependencies();
	Dependency::require_dependency_wrapper();
