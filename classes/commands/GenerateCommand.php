<?php


namespace mvc_router\commands;


use mvc_router\dependencies\Dependency;
use mvc_router\services\FileGeneration;

class GenerateCommand extends Command {
	public function dependencies() {
		if($this->param('custom-file')) {
			require_once __DIR__.'/../../'.$this->param('custom-file');
		}

		Dependency::require_dependency_wrapper();
		return 'DependencyWrapper.php has been generated !';
	}

	public function base_files(FileGeneration $generation) {
		if(!$this->param('custom-dir')) {
			$this->add_param('custom-dir', '/');
		}
		$custom_dir = $this->param('custom-dir');
		$generation->generate_base_htaccess($custom_dir);
		$generation->generate_index($custom_dir);
		$generation->generate_update_dependencies($custom_dir);
		$generation->generate_custom_autoload($custom_dir);
		return 'All default files has been generated !';
	}
}