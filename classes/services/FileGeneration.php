<?php


namespace mvc_router\services;


use Exception;

/**
 * Permet de regénérer les fichiers non versionnés à leur version d'origine si vous l'avez modifié
 *
 * @package mvc_router\services
 */
class FileGeneration extends Service {
	/** @var \mvc_router\helpers\Helpers $helpers */
	public $helpers;
	
	public function generate_index($custom_dir) {
		$slash = $this->helpers->get_slash();
		$root_path = ($this->helpers->is_unix() ? realpath(__DIR__.$slash.'..'.$slash.'..'.$slash).$slash : '').$custom_dir.$slash;
		$index = '<?php

use mvc_router\dependencies\Dependency;

require_once \''.$root_path.'autoload.php\';
require_once \''.$root_path.'htaccess.php\';

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
catch (\mvc_router\http\errors\Exception400 $e) {
	$dw->get_service_error()->error400($e->getMessage(), $e->getReturnType());
}
catch (\mvc_router\http\errors\Exception401 $e) {
	$dw->get_service_error()->error401($e->getMessage(), $e->getReturnType());
}
catch (\mvc_router\http\errors\Exception404 $e) {
	$dw->get_service_error()->error401($e->getMessage(), $e->getReturnType());
}
catch (\mvc_router\http\errors\Exception500 $e) {
	$dw->get_service_error()->error401($e->getMessage(), $e->getReturnType());
}
';

		file_put_contents(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'index.php', $index);
	}
	public function generate_base_htaccess($custom_dir) {
		$slash = $this->helpers->get_slash();
		$htaccess_php = '<?php
try {
	mvc_router\dependencies\Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_router()
		->root_route(\'routes_controller\')->inspect_controllers();
}
catch(Exception $e) {
	exit($e->getMessage());
}
';
		$htaccess_apache = 'RewriteEngine on

RewriteRule ^([^\.]+)$ /index.php?q=$0 [QSA,L]
';
		if(!is_file(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'.htaccess')) {
			file_put_contents(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'.htaccess', $htaccess_apache);
		}
		if(!is_file(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'htaccess.php')) {
			file_put_contents(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'htaccess.php', $htaccess_php);
		}
	}
	public function generate_update_dependencies($custom_dir) {
		$slash = $this->helpers->get_slash();
		$ud = '<?php

	use mvc_router\confs\Conf;
	use mvc_router\dependencies\Dependency;

	require_once __DIR__.\'/../autoload.php\';

	try {
		$yaml = Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_yaml();
		$dependencies = $yaml->parseFile(__DIR__.\'/dependencies.yaml\')->properties;
		
		$dependency = [];
		$conf = [];
		foreach( $dependencies as $type => $_dependencies ) {
			switch($type) {
				case \'add\':
					foreach( $_dependencies as $dependency_type => $modules ) {
						switch($dependency_type) {
							case \'views\':
								if(!isset($dependency[\'add_custom_views\'])) {
									$dependency[ \'add_custom_views\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$dependency[\'add_custom_views\'][] = array_merge(
										$module, [\'class\' => $module_class],
										(isset($module[\'parent\'])
											? [] : [\'parent\' => \'\mvc_router\mvc\View\'])
									);
								}
								break;
							case \'data_models\':
								if(!isset($dependency[ \'add_custom_data_models\' ])) {
									$dependency[ \'add_custom_data_models\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$dependency[ \'add_custom_data_models\' ][] = array_merge([
										\'class\' => [
											\'name\' => $module_class,
											\'type\' => $module[\'type\']
										],
										\'name\' => $module[\'name\'],
										\'file\' => $module[\'file\'],
									], (isset($module[\'parent\'])
										? [] : ($module[\'type\'] === \'entity\'
											? [\'parent\' => \'\mvc_router\data\gesture\Entity\']
												: [\'parent\' => \'\mvc_router\data\gesture\Manager\'])));
								}
								break;
							case \'controllers\':
								if(!isset($dependency[ \'add_custom_controllers\' ])) {
									$dependency[ \'add_custom_controllers\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$dependency[ \'add_custom_controllers\' ][] = array_merge(
										[ \'class\' => $module_class ], $module,
										(isset($module[\'parent\'])
											? [] : [\'parent\' => \'\mvc_router\mvc\Controller\'])
									);
								}
								break;
							case \'ws_controllers\':
								if(!isset($dependency[ \'add_custom_ws_controllers\' ])) {
									$dependency[ \'add_custom_ws_controllers\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$dependency[ \'add_custom_ws_controllers\' ][] = array_merge(
										[ \'class\' => $module_class ],
										$module,
										(isset($module[\'parent\'])
											? [] : [\'parent\' => \'\mvc_router\websockets\MessageComponent\'])
									);
								}
								break;
							case \'confs\':
								if(!isset($conf[ \'add_custom_confs\' ])) {
									$conf[ \'add_custom_confs\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$conf[ \'add_custom_confs\' ][] = array_merge( [ \'class\' => $module_class ], $module );
								}
								break;
							case \'services\':
								if(!isset($dependency[ \'add_custom_controllers\' ])) {
									$dependency[ \'add_custom_services\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$dependency[ \'add_custom_services\' ][] = array_merge(
										[ \'class\' => $module_class ], $module,
										(isset($module[\'parent\'])
											? [] : [\'parent\' => \'\mvc_router\services\Service\'])
									);
								}
								break;
							default: break;
						}
					}
					break;
				case \'extends\':
					foreach( $_dependencies as $dependency_type => $modules ) {
						switch($dependency_type) {
							case \'confs\':
								if( !isset($conf[ \'extend_confs\' ]) ) {
									$conf[ \'extend_confs\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$conf[ \'extend_confs\' ][] = $module;
								}
								break;
							case \'services\':
								if( !isset($conf[ \'extend_services\' ]) ) {
									$dependencies[ \'extend_services\' ] = [];
								}
								foreach( $modules as $module_class => $module ) {
									$module[\'file\'] = str_replace(\'__DIR__\', __DIR__, $module[\'file\']);
									$dependencies[ \'extend_services\' ][] = $module;
								}
								break;
							default: break;
						}
					}
					break;
				default: break;
			}
		}
		
		foreach( $dependency as $method => $modules ) {
			Dependency::$method(...$modules);
		}
		foreach( $conf as $method => $modules ) {
			Conf::$method(...$modules);
		}
		
	} catch( Exception $e ) {
		exit($e->getMessage());
	}
';

		if(!is_file(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'update_dependencies.php')) {
			file_put_contents(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'update_dependencies.php', $ud);
		}
	}
	public function generate_custom_autoload($custom_dir) {
		$slash = $this->helpers->get_slash();
		$autoload = '<?php

use mvc_router\dependencies\Dependency;

const __SITE_NAME__ = \''.$custom_dir.'\';
if(is_file(__DIR__.\'/update_dependencies.php\')) {
	require_once __DIR__.\'/update_dependencies.php\';
}

Dependency::get_wrapper_factory()->get_dependency_wrapper()->get_triggers()->initialize();
';

		file_put_contents(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'autoload.php', $autoload);
	}
	public function generate_gitignore($custom_dir) {
		$slash = $this->helpers->get_slash();
		$gitingore = '.htaccess
autoload.php
htaccess.php
index.php
update_dependencies.php';
		if(!realpath(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir.$slash.'.gitignore')) {
			file_put_contents(realpath(($this->helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$custom_dir).$slash.'.gitignore', $gitingore);
		}
	}
	/**
	 * @param $custom_dir
	 * @throws Exception
	 */
	public function generate_mysql_conf_file($custom_dir) {
		$fs = $this->inject->get_service_fs();
		$slash = $this->helpers->get_slash();

		if(!is_file(($this->helpers->is_unix() ? __DIR__."{$slash}..{$slash}..{$slash}" : '')."{$custom_dir}{$slash}classes{$slash}confs{$slash}mysql.json")) {
			$fs->create_file(($this->helpers->is_unix() ? __DIR__."{$slash}..{$slash}..{$slash}" : '')."{$custom_dir}{$slash}classes{$slash}confs", 'mysql', FileSystem::JSON, null, [
				"host" => '',
				"user" => '',
				"pass" => "",
				"user_prefix" => '',
				"db_prefix" => '',
				"db_name" => "",
				"port" => 3306,
			]);
		}
	}
}