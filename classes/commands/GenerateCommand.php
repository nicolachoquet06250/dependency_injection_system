<?php


namespace mvc_router\commands;


use mvc_router\dependencies\Dependency;

class GenerateCommand extends Command {
	public function dependencies() {
		if($this->param('custom-file')) {
			require_once __DIR__.'/../../'.$this->param('custom-file');
		}

		Dependency::require_dependency_wrapper();
		return 'DependencyWrapper.php has been generated !';
	}

	private function generate_index() {
		$index = '<?php

use mvc_router\dependencies\Dependency;

require_once \''.(realpath(__DIR__.'/../../')).$this->param('custom-dir').'/autoload.php\';
require_once \''.(realpath(__DIR__.'/../../')).$this->param('custom-dir').'/update_dependencies.php\';
require_once \''.(realpath(__DIR__.'/../../')).$this->param('custom-dir').'/htaccess.php\';

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

		file_put_contents(__DIR__.'/../../'.$this->param('custom-dir').'/index.php', $index);
	}
	private function generate_base_htaccess() {
		$htaccess_php = '<?php

mvc_router\dependencies\Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_router()
	->root_route(\'routes_controller\')->inspect_controllers();
';
		$htaccess_apache = 'RewriteEngine on

RewriteRule ^([^\.]+)$ /index.php?q=$0 [QSA,L]
';
		if(!is_file(__DIR__.'/../../'.$this->param('custom-dir').'/.htaccess')) {
			file_put_contents(__DIR__.'/../../'.$this->param('custom-dir').'/.htaccess', $htaccess_apache);
		}
		if(!is_file(__DIR__.'/../../'.$this->param('custom-dir').'/htaccess.php')) {
			file_put_contents(__DIR__.'/../../'.$this->param('custom-dir').'/htaccess.php', $htaccess_php);
		}
	}
	private function generate_update_dependencies() {
		$ud = '<?php

	use mvc_router\dependencies\Dependency;

	require_once \''.(realpath(__DIR__.'/../../')).'/autoload.php\';

	// parameters are arrays
	Dependency::add_custom_controllers();
';

		if(!is_file(__DIR__.'/../../'.$this->param('custom-dir').'/update_dependencies.php')) {
			file_put_contents(__DIR__.'/../../'.$this->param('custom-dir').'/update_dependencies.php', $ud);
		}
	}
	private function generate_custom_autoload() {
		$autoload = '<?php
	require_once __DIR__.\'/../autoload.php\';
';

		file_put_contents(__DIR__.'/../../'.$this->param('custom-dir').'/autoload.php', $autoload);
	}
	public function base_files() {
		if(!$this->param('custom-dir')) {
			$this->add_param('custom-dir', '/');
		}
		$this->generate_base_htaccess();
		$this->generate_index();
		$this->generate_update_dependencies();
		$this->generate_custom_autoload();
		return 'All default files has been generated !';
	}
}