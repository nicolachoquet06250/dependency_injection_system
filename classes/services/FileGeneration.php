<?php


namespace mvc_router\services;


class FileGeneration extends Service {
	public function generate_index($custom_dir) {
		$index = '<?php

use mvc_router\dependencies\Dependency;

require_once \''.(realpath(__DIR__.'/../../')).$custom_dir.'/autoload.php\';
require_once \''.(realpath(__DIR__.'/../../')).$custom_dir.'/update_dependencies.php\';
require_once \''.(realpath(__DIR__.'/../../')).$custom_dir.'/htaccess.php\';

$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();
try {
	$request_uri = isset($_GET[\'q\']) ?
		(substr($_GET[\'q\'], 0, 1) !== \'/\' ? \'/\'.$_GET[\'q\'] : $_GET[\'q\'])
		: $_SERVER[\'REQUEST_URI\'];
	if(substr($request_uri, 0, 10) === \'/index.php\') {
		$request_uri = str_replace(\'/index.php\', \'\', $request_uri);
	}

	echo $dw->get_router()->execute($request_uri);
}
catch (Exception $e) {
	$dw->get_service_error()->error500($e->getMessage());
}
catch (Error $e) {
	$dw->get_service_error()->error500($e->getMessage());
}
';

		file_put_contents(__DIR__.'/../../'.$custom_dir.'/index.php', $index);
	}
	public function generate_base_htaccess($custom_dir) {
		$htaccess_php = '<?php

mvc_router\dependencies\Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_router()
	->root_route(\'routes_controller\')->inspect_controllers();
';
		$htaccess_apache = 'RewriteEngine on

RewriteRule ^([^\.]+)$ /index.php?q=$0 [QSA,L]
';
		if(!is_file(__DIR__.'/../../'.$custom_dir.'/.htaccess')) {
			file_put_contents(__DIR__.'/../../'.$custom_dir.'/.htaccess', $htaccess_apache);
		}
		if(!is_file(__DIR__.'/../../'.$custom_dir.'/htaccess.php')) {
			file_put_contents(__DIR__.'/../../'.$custom_dir.'/htaccess.php', $htaccess_php);
		}
	}
	public function generate_update_dependencies($custom_dir) {
		$ud = '<?php

	use mvc_router\dependencies\Dependency;

	require_once \''.(realpath(__DIR__.'/../../')).'/autoload.php\';

	// parameters are arrays
	Dependency::add_custom_controllers();
';

		if(!is_file(__DIR__.'/../../'.$custom_dir.'/update_dependencies.php')) {
			file_put_contents(__DIR__.'/../../'.$custom_dir.'/update_dependencies.php', $ud);
		}
	}
	public function generate_custom_autoload($custom_dir) {
		$autoload = '<?php
	require_once __DIR__.\'/../autoload.php\';
';

		file_put_contents(__DIR__.'/../../'.$custom_dir.'/autoload.php', $autoload);
	}
}