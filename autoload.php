<?php

	use mvc_router\dependencies\Dependency;

	require_once __DIR__.'/classes/Dependency.php';

	register_shutdown_function( "fatal_handler" );

	spl_autoload_register([Dependency::class, 'autoload'], true);

	function format_error( $errno, $errstr, $errfile, $errline ) {
		$trace = print_r( debug_backtrace( false ), true );
		$content = "<b style='color: red;'>Error( $errstr; code( $errno ); file( $errfile ); line( $errline ); <pre>$trace</pre> )</b>";
		return $content;
	}
	function fatal_handler() {
		$error = error_get_last();

		if(!is_null($error)) {
			$errno   = $error["type"];
			$errfile = $error["file"];
			$errline = $error["line"];
			$errstr  = $error["message"];

			echo format_error( $errno, $errstr, $errfile, $errline);
		}
	}

	Dependency::load_base_dependencies();
	Dependency::require_dependency_wrapper();
