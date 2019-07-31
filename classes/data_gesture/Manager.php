<?php


namespace mvc_router\data\gesture;


use Exception;
use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use ReflectionClass;
use ReflectionException;

abstract class Manager extends Base {
	protected $entity_class = null;

	protected function get_from(array $keys, array $value) {}
	protected function get_keys_from(array $search_keys, array $from_keys, array $value) {}

	protected function get_table() {
		$class = get_class($this);
		$class = str_replace('\\', '/', $class);
		$class = explode('/', $class);
		$class = $class[count($class) - 1];
		return strtolower($class);
	}

	/**
	 * @return Entity
	 * @throws Exception
	 */
	public function get_entity() {
		if($this->entity_class) {
			if(Dependency::is_in($this->entity_class)) {
				$entity_name = Dependency::get_name_from_class($this->entity_class);
				$method      = 'get_'.$entity_name;
				return $this->inject->$method();
			}
		}
		throw new Exception($this->inject->get_service_translation()->__("L'entité %1 n'à pas été reconnu !", [$this->entity_class]));
	}

	/**
	 * @param string $name
	 * @param array  ...$arguments
	 * @return mixed|null
	 * @throws ReflectionException
	 * @throws Exception
	 */
	protected function call($name, ...$arguments) {
		$class_ref = new ReflectionClass(get_class($this));
		$doc = $class_ref->getDocComment();
		$doc = str_replace(['/**', ' */', ' * ', ' *'], '', $doc);
		$doc = explode("\n", $doc);
		$virtual_methods = [];
		foreach ($doc as $i => $item) {
			if($item !== '' && strstr($item, '@method ') !== false) {
				$virtual_method = explode('(', $item)[0];
				$method = explode(' ', $virtual_method)[2];
				$params = explode('(', str_replace(')', '', $item))[1];
				$params = explode(', ', $params);
				$params = array_map(function($param) {
					return explode(' ', $param)[0];
				}, $params);
				$virtual_methods[] = [
					'name' 			=> $method,
					'params_type' 	=> $params
				];
			}
		}
		$method_exists = false;
		$current_method = null;
		foreach ($virtual_methods as $virtual_method) {
			if($virtual_method['name'] === $name) {
				$method_exists = true;
				$current_method = $virtual_method;
				break;
			}
		}
		if(!$method_exists) {
			return parent::call($name, ...$arguments);
		}
		$mysqli = $this->confs->get_mysql();
		$method_name = $current_method['name'];
		$method_params = $current_method['params_type'];
		if(count($arguments) < count($method_params)) {
			throw new Exception($this->inject->get_service_translation()
											 ->__(get_class($this).'::'.$method_name.'() requis '.count($method_params).' et vous en avez renseigné '.count($arguments)));
		}
		$fields = explode('_from_', $method_name)[0];
		$fields = str_replace('get_', '', $fields);
		$fields = explode('_', $fields);

		$from_keys = explode('_from_', $method_name)[1];
		$from_keys = explode('_', $from_keys);
		foreach ($from_keys as $i => $from_key) {
			$from_keys[$from_key] = $arguments[$i];
			unset($from_keys[$i]);
		}
		$key_string = [];
		foreach ($from_keys as $from_key => $from_key_value) {
			if(is_string($from_key_value)) {
				$from_key_value = '"'.$from_key_value.'"';
			}
			if(is_array($from_key_value)) {
				$from_key_value = '"'.implode(', ', $from_key_value).'"';
			}
			$key_string[] = '`'.$from_key.'`='.$from_key_value;
		}
		$mysqli->query('SELECT `'.implode('`, `', $fields).'` FROM '.$this->get_table().' WHERE '.implode(' AND ', $key_string));
		return true;
	}
}