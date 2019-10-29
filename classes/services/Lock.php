<?php


namespace mvc_router\services;


/**
 * Permet de bloquer l'écriture d'un fichier ou d'un répertoire jusqu'à ce qu'il soit débloqué.
 * Ce service peux servire pour l'utilisation de Queues par example
 *
 * @package mvc_router\services
 */
class Lock extends Service {
	protected $lock_dir = __DIR__.'/../../classes/lock';
	protected $lock_extension = '.lock';

	protected $lock_functions_file 		= 'functions';
	protected $lock_directories_file 	= 'directories';
	protected $lock_objects_file 		= 'objects';
	protected $lock_files_file 			= 'files';
	protected $lock_actions_file 		= 'actions';

	const FUNCTIONS 	= 'functions';
	const DIRECTORIES 	= 'directories';
	const OBJECTS   	= 'object';
	const FILES			= 'files';
	const ACTIONS		= 'actions';

	private function get_lock_file_path($type) {
		return $this->lock_dir.'/'.$this->{'lock_'.$type.'_file'}.$this->lock_extension;
	}
	
	protected function create_lock_dir_if_not_exists() {
		if(!realpath($this->lock_dir)) {
			mkdir($this->lock_dir, 0777, true);
		}
	}

	protected function get_type_and_key($element) {
		$type = $key = '';
		if(is_string($element)) {
			if(strstr($element, '::')) {
				$key = 'function::'.$element;
				$type = self::FUNCTIONS;
			} elseif (strstr($element, '.')) {
				$key = 'action::'.$element;
				$type = self::FILES;
			} else {
				$key = 'file::'.$element;
				$type = self::ACTIONS;
			}
		} elseif (is_object($element)) {
			$key = 'object::'.get_class($element);
			$type = self::OBJECTS;
		}

		return [$type, $key];
	}

	protected function get_lock_file($type = self::DIRECTORIES) {
		$json = $this->inject->get_service_json();
		$path = $this->get_lock_file_path($type);
		if(!is_file($path)) {
			file_put_contents($path, $json->encode(new \stdClass()));
		}
		return $json->decode(file_get_contents($path), true);
	}

	protected function update_lock_file($new_content, $type = self::DIRECTORIES) {
		$json = $this->inject->get_service_json();
		$path = $this->get_lock_file_path($type);
		if($json->encode($new_content) === $json->encode($this->get_lock_file($type))) {
			return false;
		}
		return (bool)file_put_contents($path, $json->encode($new_content));
	}

	/**
	 * @param mixed $element
	 * @return bool
	 */
	public function is_locked($element) {
		[$type, $key] = $this->get_type_and_key($element);
		$old_content = $this->get_lock_file($type);
		if(!($type && $key) || !isset($old_content[$key])) {
			return false;
		}
		return $old_content[$key];
	}

	/**
	 * @param mixed $element
	 * @return bool
	 */
	public function lock($element) {
		$this->create_lock_dir_if_not_exists();
		[$type, $key] = $this->get_type_and_key($element);
		$old_content = $this->get_lock_file($type);
		if(!($type && $key)) {
			return false;
		}
		$old_content[$key] = true;

		return $this->update_lock_file($old_content, $type);
	}

	/**
	 * @param mixed $element
	 * @return bool
	 */
	public function unlock($element) {
		$this->create_lock_dir_if_not_exists();
		[$type, $key] = $this->get_type_and_key($element);
		$old_content = $this->get_lock_file($type);
		if(!($type && $key)) {
			return false;
		}
		$old_content[$key] = false;

		return $this->update_lock_file($old_content, $type);
	}
}