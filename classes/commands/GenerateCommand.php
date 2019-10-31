<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\confs\Conf;
use mvc_router\dependencies\Dependency;
use mvc_router\helpers\Helpers;
use mvc_router\services\Translate;
use mvc_router\services\Yaml;

class GenerateCommand extends Command {
	/**
	 * @syntax [site] generate:dependencies
	 *
	 * @param Helpers $helpers
	 * @return string
	 */
	public function dependencies(Helpers $helpers) {
		$slash = $helpers->get_slash();
		$update_dependencies_path = ($helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').__SITE_NAME__.'/update_dependencies.php';
		if(is_file($update_dependencies_path)) require_once $update_dependencies_path;
		else $this->inject->get_service_logger()
			->log('WARNING: file '.$update_dependencies_path.' not found !');

		Dependency::require_dependency_wrapper();
		Conf::require_conf_wrapper();
		return 'DependencyWrapper.php and ConfWrapper.php has been generated !';
	}

	/**
	 * @syntax [custom-dir] generate:base_files
	 *
	 * @return string
	 * @throws Exception
	 */
	public function base_files() {
		$generation = $this->inject->get_service_generation();
		$generation->generate_base_htaccess();
		$generation->generate_index();
		$generation->generate_update_dependencies();
		$generation->generate_custom_autoload();
		$generation->generate_gitignore();
		$generation->generate_mysql_conf_file();
		return 'All default files has been generated ! Don\'t forget to fill the classes/confs/mysql.json file';
	}
	
	/**
	 * @syntax generate:translations
	 *
	 * @param Translate $translation
	 * @param Helpers $helpers
	 * @return mixed|string
	 */
	public function translations(Translate $translation, Helpers $helpers) {
		$translation->initialize_translation_files();
		$slash = $helpers->get_slash();

		$this->parcoure_dir(realpath(__DIR__.$slash.'..'.$slash.'..'.$slash), $translation, $helpers);

		$languages = $translation->get_languages();
		$languages_imploded = implode(', ', array_keys($languages));
		if(count($languages) > 1) {
			return $translation->__('Les langues %1 ont bien été générés', [$languages_imploded]);
		}
		else {
			return $translation->__('La langue %1 à bien été généré', [$languages_imploded]);
		}
	}
	
	/**
	 * @param $directory
	 * @param Translate $translation
	 * @param Helpers $helpers
	 */
	private function parcoure_dir($directory, Translate $translation, Helpers $helpers) {
		$dir = opendir($directory);

		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..' && substr($elem, 0, 1) !== '.') {
				if(is_file($directory.$helpers->get_slash().$elem)) {
					$file_content = file_get_contents($directory.'/'.$elem);
					if(strstr($file_content, '->__(')) {
						preg_match_all('/\-\>\_\_\([\'|"](.+)[\'|"](, ?\[.*\])?\)[,|, ]?/m', $file_content, $matches);
						$matches = $matches[1];
						foreach ($matches as $match) {
							$translation->write_translated($match);
						}
					}
				}
				elseif (is_dir($directory.$helpers->get_slash().$elem)) {
					$this->parcoure_dir($directory.'/'.$elem, $translation, $this->inject->get_helpers());
				}
			}
		}
	}
	
	/**
	 * @syntax [site] generate:service -p [name=<value>] [is_singleton=<value>?]
	 *
	 * @param Helpers $helpers
	 * @param Yaml $yaml
	 * @return string
	 * @throws Exception
	 */
	public function service(Helpers $helpers, Yaml $yaml) {
		$class = $this->param('name');
		$site = __SITE_NAME__;
		$singleton = $this->param('is_singleton');
		
		$slash = $helpers->get_slash();
		
		$yaml_dependencies_path = __DIR__.$slash.'..'.$slash.'..'.$slash.$site.$slash.'dependencies.yaml';
		
		$yaml_dependencies = $yaml->decode_from_file($yaml_dependencies_path);
		
		if(!isset($yaml_dependencies['add'])) {
			$yaml_dependencies['add'] = [];
		}
		if(!isset($yaml_dependencies['add']['services'])) {
			$yaml_dependencies['add']['services'] = [];
		}
		
		$yaml_dependencies['add']['services']["\mvc_router\services\\".ucfirst($class)] = [
			'name' => "service_{$class}",
			'file' => "__DIR__{$slash}..{$slash}..{$slash}{$site}{$slash}classes{$slash}services{$slash}".ucfirst($class).".php",
			'is_singleton' => $singleton,
			'parent' => '\mvc_router\services\Service'
		];
		
		$service = '<?php
		
	namespace mvc_router\services;
	'.($singleton ? "\n\tuse \mvc_router\interfaces\Singleton;" : '').'
	
	class '.ucfirst($class).' extends Service'.($singleton ? ' implements Singleton' : '').' {';
	if($singleton) {
		$service .= '
		private static $instance;
	
		public static function create() {
			if(is_null(self::$instance)) {
				self::$instance = new '.ucfirst($class).'();
			}
			return self::$instance;
		}
	
	';
	}
	$service .= '}
	';
		
		$path = __DIR__.$slash.'..'.$slash.'..'.$slash.$site.$slash.'classes'.$slash.'services'.$slash.ucfirst($class).'.php';
		file_put_contents($path, $service);
		file_put_contents($yaml_dependencies_path, $yaml->encode($yaml_dependencies));
		
		$this->inject->get_commands()->run('install:update');
		
		return 'Le service '.$class.' à été généré et intégré dans dependencies.yaml avec succès !';
	}
	
	/**
	 * @syntax [site] generate:customized_service -p [name=<value>] [is_singleton=<value>?]
	 *
	 * @param Helpers $helpers
	 * @param Yaml $yaml
	 * @return string
	 * @throws Exception
	 */
	public function customized_service(Helpers $helpers, Yaml $yaml) {
		$name = $this->param('name');
		$site = __SITE_NAME__;
		$singleton = $this->param('is_singleton');
		
		$slash = $helpers->get_slash();
		
		$yaml_dependencies_path = __DIR__.$slash.'..'.$slash.'..'.$slash.$site.$slash.'dependencies.yaml';
		
		$yaml_dependencies = $yaml->decode_from_file($yaml_dependencies_path);
		
		if(!isset($yaml_dependencies['extends']['services'])) {
			$yaml_dependencies['extends']['services'] = [];
		}
		
		$old_class = $this->inject::get_class_from_name($name);
		$class_base = explode('\\', $old_class)[count(explode('\\', $old_class)) - 1];
		
		$yaml_dependencies['extends']['services'][$old_class] = [
			'class' => [
				'old' => $old_class,
				'new' => '\mvc_router\services\custom\\'.$class_base,
			],
			'name' => $name,
			'file' => "__DIR__{$slash}{$site}{$slash}classes{$slash}services{$slash}".$class_base.".php",
			'is_singleton' => $singleton,
		];
		
		$service = '<?php
		
	namespace mvc_router\services\custom;
	'.($singleton ? "\n\tuse \mvc_router\interfaces\Singleton;" : '').'
	
	class '.$class_base.' extends \\'.$old_class.($singleton ? ' implements Singleton' : '').' {';
		if($singleton) {
			$service .= '
		private static $instance;
	
		public static function create() {
			if(is_null(self::$instance)) {
				self::$instance = new '.$class_base.'();
			}
			return self::$instance;
		}
	
	';
		}
		$service .= '}
	';
		
		$path = __DIR__.$slash.'..'.$slash.'..'.$slash.$site.$slash.'classes'.$slash.'services'.$slash.$class_base.'.php';
		file_put_contents($path, $service);
		file_put_contents($yaml_dependencies_path, $yaml->encode($yaml_dependencies));
		
		$this->inject->get_commands()->run('install:update');
		
		return 'Le service '.$name.' à été généré en extension du service de base et intégré dans dependencies.yaml avec succès !';
		
	}
}