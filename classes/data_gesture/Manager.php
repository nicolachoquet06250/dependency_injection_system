<?php


namespace mvc_router\data\gesture;


use Exception;
use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use ReflectionClass;
use ReflectionException;

abstract class Manager extends Base {
	protected $entity_class = null;

	protected final function get_mysql() {
		return $this->confs->get_mysql();
	}

	/**
	 * @param array $keys
	 * @param array $values
	 * @return Base[]|Base
	 * @throws ReflectionException
	 * @throws Exception
	 */
	protected function get_from(array $keys, array $values) {
		$mysqli = $this->get_mysql();
		$key_string = [];
		foreach ($keys as $i => $key) {
			$value = $values[$i];
			if (is_string($value)) {
				$value = '"'.$value.'"';
			}
			if (is_array($value)) {
				$value = '"'.implode(', ', $value).'"';
			}
			$key_string[] = '`'.$key.'`='.$value;
		}
		$mysqli->query('SELECT * FROM `'.$this->get_table().'` WHERE '.implode(' AND ', $key_string));
		return $mysqli->fetch_object(Dependency::get_name_from_class($this->get_entity()->get_class()));
	}

	/**
	 * @param array $search_keys
	 * @param array $from_keys
	 * @param array $values
	 * @return Base[]|Base
	 * @throws ReflectionException
	 * @throws Exception
	 */
	protected function get_keys_from(array $search_keys, array $from_keys, array $values) {
		if(empty($search_keys)) {
			return [];
		}
		$mysqli = $this->get_mysql();
		$key_string = [];
		foreach ($from_keys as $i => $key) {
			$value = $values[$i];
			if (is_string($value)) {
				$value = '"'.$value.'"';
			}
			if (is_array($value)) {
				$value = '"'.implode(', ', $value).'"';
			}
			$key_string[] = '`'.$key.'`='.$value;
		}
		$mysqli->query('SELECT `'.implode('`, `', $search_keys).'` FROM `'.$this->get_table().'` WHERE '.implode(' AND ', $key_string));
		return $mysqli->fetch_object(Dependency::get_name_from_class($this->get_entity()->get_class()));
	}

	/**
	 * @param string $method_name
	 * @param mixed[] ...$arguments
	 * @return Base|Base[]
	 * @throws ReflectionException
	 */
	protected function select($method_name, ...$arguments) {
		$fields = explode('_from_', $method_name)[0];
		$fields = str_replace(['get_', 'select_', 'find_'], '', $fields);
		$fields = explode('_', $fields);

		$from_keys = explode('_from_', $method_name)[1];
		$from_keys = explode('_', $from_keys);
		foreach ($from_keys as $i => $from_key) {
			$from_keys[$from_key] = $arguments[$i];
			unset($from_keys[$i]);
		}
		return count($fields) === 1 && $fields[0] === 'all'
			? $this->get_from(array_keys($from_keys), array_values($from_keys))
			: $this->get_keys_from($fields, array_keys($from_keys), array_values($from_keys));
	}

	/**
	 * @return string
	 */
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
	 * @return array|Base
	 * @throws ReflectionException
	 */
	public function get_all() {
		$mysqli = $this->confs->get_mysql();
		$mysqli->query('SELECT * FROM `'.$this->get_table().'`');
		return $mysqli->fetch_object(Dependency::get_name_from_class($this->entity_class));
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
		$method_name = $current_method['name'];
		$method_params = $current_method['params_type'];
		if(count($arguments) < count($method_params)) {
			throw new Exception($this->inject->get_service_translation()
											 ->__(get_class($this).'::'.$method_name.'() requis '.count($method_params).' et vous en avez renseigné '.count($arguments)));
		}
		$is_select = substr($name, 0, strlen('get_')) === 'get_'
					 || substr($name, 0, strlen('select_')) === 'select_'
					 || substr($name, 0, strlen('find_')) === 'find_';
		$is_delete = substr($name, 0, strlen('del_')) === 'del_'
					 || substr($name, 0, strlen('delete_')) === 'delete_'
					 || substr($name, 0, strlen('remove_')) === 'remove_';
		$is_update = substr($name, 0, strlen('update_')) === 'update_';
		$is_insert = substr($name, 0, strlen('insert_')) === 'insert_'
					 || substr($name, 0, strlen('set_')) === 'set_';
		if($is_select) {
			return $this->select($method_name, ...$arguments);
		} elseif ($is_delete) {
			throw new Exception('Le type de requête `DELETE` n\'à pas encore été développé !');
		} elseif ($is_update) {
			throw new Exception('Le type de requête `UPDATE` n\'à pas encore été développé !');
		} elseif ($is_insert) {
			throw new Exception('Le type de requête `INSERT` n\'à pas encore été développé !');
		}
		return null;
	}
}