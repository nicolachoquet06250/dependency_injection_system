<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\confs\Conf;
use mvc_router\dependencies\Dependency;
use mvc_router\helpers\Helpers;
use mvc_router\services\Translate;

class GenerateCommand extends Command {
	/**
	 * @param Helpers $helpers
	 * @return string
	 */
	public function dependencies(Helpers $helpers) {
		$slash = $helpers->get_slash();
		if($this->param('custom-file') && is_file($this->param('custom-file'))) {
			require_once ($helpers->is_unix() ? __DIR__.$slash.'..'.$slash.'..'.$slash : '').$this->param('custom-file');
		}
		else {
			$this->inject->get_service_logger()
						 ->log('WARNING: file '.realpath(__DIR__.$slash.'..'.$slash.'..'.$slash.$this->param('custom-file')).' not found !');
		}

		Dependency::require_dependency_wrapper();
		Conf::require_conf_wrapper();
		return 'DependencyWrapper.php and ConfWrapper.php has been generated !';
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function base_files() {
		$generation = $this->inject->get_service_generation();
		if(!$this->param('custom-dir')) {
			$this->add_param('custom-dir', '');
		}
		$custom_dir = $this->param('custom-dir');
		$generation->generate_base_htaccess($custom_dir);
		$generation->generate_index($custom_dir);
		$generation->generate_update_dependencies($custom_dir);
		$generation->generate_custom_autoload($custom_dir);
		$generation->generate_gitignore($custom_dir);
		$generation->generate_mysql_conf_file($custom_dir);
		return 'All default files has been generated ! Don\'t forget to fill the classes/confs/mysql.json file';
	}

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
	
	public function service(Helpers $helpers) {
		$class = $this->param('name');
		
		$service = '<?php
		
	namespace mvc_router\services;
	
	class '.ucfirst($class).' extends Service {}
	';
		
		$path = __DIR__.$helpers->get_slash().ucfirst($class).'Command.php';
		file_put_contents($path, $service);
		
		return 'Le service '.$class.' à été généré avec succès !';
	}
}