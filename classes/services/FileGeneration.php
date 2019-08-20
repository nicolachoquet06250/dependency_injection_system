<?php


namespace mvc_router\services;


class FileGeneration extends Service {
	public function generate_index($custom_dir) {
		$index = '<?php

use mvc_router\dependencies\Dependency;

require_once \''.(realpath(__DIR__.'/../../')).'/'.$custom_dir.'/autoload.php\';
require_once \''.(realpath(__DIR__.'/../../')).'/'.$custom_dir.'/update_dependencies.php\';
require_once \''.(realpath(__DIR__.'/../../')).'/'.$custom_dir.'/htaccess.php\';

$dw = Dependency::get_wrapper_factory()->get_dependency_wrapper();
try {
	$request_uri = isset($_GET[\'q\']) ?
		(substr($_GET[\'q\'], 0, 1) !== \'/\' ? \'/\'.$_GET[\'q\'] : $_GET[\'q\'])
		: $_SERVER[\'REQUEST_URI\'];
	if(substr($request_uri, 0, 10) === \'/index.php\') {
		$request_uri = str_replace(\'/index.php\', \'\', $request_uri);
	}

	$router_return = $dw->get_router()->execute($request_uri);
	if(gettype($router_return) === \'object\') {
		if(Dependency::is_view(Dependency::get_name_from_class(get_class($router_return)))) echo $router_return;
		else {
			$errors = $dw->get_service_error();
			$translation = $dw->get_service_translation();
			$errors->error404(
				$translation->__(\'La vue %1 n\\\'à pas été reconnu !\', [
					Dependency::get_name_from_class(get_class($router_return))
				])
			);
		}
	}
	else {
		echo $router_return;
	}
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

	use mvc_router\confs\Conf;
	use mvc_router\dependencies\Dependency;

	require_once __DIR__.\'/../autoload.php\';

	// parameters are arrays
	Dependency::add_custom_controllers();
	
	// parameters are arrays
	Conf::extend_confs();
';

		if(!is_file(__DIR__.'/../../'.$custom_dir.'/update_dependencies.php')) {
			file_put_contents(__DIR__.'/../../'.$custom_dir.'/update_dependencies.php', $ud);
		}
	}
	public function generate_custom_autoload($custom_dir) {
		$autoload = '<?php

use mvc_router\dependencies\Dependency;

const __SITE_NAME__ = \''.$custom_dir.'\';
require_once __DIR__.\'/update_dependencies.php\';

Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_triggers()->initialize();
';

		file_put_contents(__DIR__.'/../../'.$custom_dir.'/autoload.php', $autoload);
	}
	public function generate_gitignore($custom_dir) {
		$gitingore = '.htaccess
autoload.php
htaccess.php
index.php';
		if(!realpath(__DIR__.'/../../'.$custom_dir.'/.gitignore')) {
			file_put_contents(realpath(__DIR__.'/../../'.$custom_dir).'/.gitignore', $gitingore);
		}
	}
}