<?php


namespace mvc_router\data\gesture;


use Exception;
use mvc_router\Base;
use mvc_router\confs\Mysql;
use mvc_router\dependencies\Dependency;
use ReflectionClass;
use ReflectionException;

abstract class Manager extends Base {
	protected $entity_class = null;

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	protected final function get_virtual_methods() {
		$class_ref = new ReflectionClass(get_class($this));
		$doc = $class_ref->getDocComment();
		$doc = str_replace(['/**', ' */', ' * ', ' *'], '', $doc);
		$doc = explode("\n", $doc);
		$virtual_methods = [];
		foreach ($doc as $i => $item) {
			if($item !== '' && strstr($item, '@method ') !== false) {
				$virtual_method = explode('(', $item)[0];
				str_replace("\t", ' ', $virtual_method);
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
		return $virtual_methods;
	}

	protected function get_sql_string_from_array(array $fields) {
		$key_string = [];
		foreach ($fields as $key => $value) {
			if (is_string($value)) {
				$value = '"'.$value.'"';
			}
			if (is_array($value)) {
				$value = '"'.implode(', ', $value).'"';
			}
			$key_string[] = '`'.$key.'`='.$value;
		}
		return $key_string;
	}

	/**
	 * @param string $method
	 * @return bool|mixed
	 * @throws ReflectionException
	 */
	protected final function has_virtual_method($method) {
		$virtual_methods = $this->get_virtual_methods();
		$current_method = null;
		foreach ($virtual_methods as $virtual_method) {
			if($virtual_method['name'] === $method) {
				return $virtual_method;
			}
		}
		return false;
	}

	protected final function get_mysql() {
		return $this->confs->get_mysql();
	}

	/**
	 * @param array $keys
	 * @param array $values
	 * @return Entity[]|Entity
	 * @throws ReflectionException
	 * @throws Exception
	 */
	protected function get_from(array $keys, array $values) {
		$mysqli = $this->get_mysql();
		$keys = $this->associate_keys_and_values($keys, $values);
		return $mysqli->query('SELECT * FROM `'.$this->get_table().'` WHERE '.implode(' AND ', $this->get_sql_string_from_array($keys)))
			->fetch_object(Dependency::get_name_from_class($this->get_entity()->get_class()));
	}

	/**
	 * @param array $search_keys
	 * @param array $from_keys
	 * @param array $values
	 * @return Entity[]|Entity
	 * @throws ReflectionException
	 * @throws Exception
	 */
	protected function get_keys_from(array $search_keys, array $from_keys, array $values) {
		if(empty($search_keys)) {
			return [];
		}
		$mysqli = $this->get_mysql();
		$from_keys = $this->associate_keys_and_values($from_keys, $values);
		return $mysqli->query('SELECT `'.implode('`, `', $search_keys).'` FROM `'.$this->get_table().'` WHERE '.implode(' AND ', $this->get_sql_string_from_array($from_keys)))
			->fetch_object(Dependency::get_name_from_class($this->get_entity()->get_class()));
	}

	/**
	 * @param array $keys
	 * @param array $values
	 * @return array
	 * @throws Exception
	 */
	protected final function associate_keys_and_values(array $keys, array $values) {
		foreach ($keys as $i => $key) {
			$_key = null;
			$_key = $this->get_associate_entity_property($key);
			$keys[($_key ?? $key)] = $values[$i];
			unset($keys[$i]);
		}
		return $keys;
	}

	/**
	 * @param string $key
	 * @return bool|string|null
	 * @throws Exception
	 */
	protected final function get_associate_entity_property($key) {
		return $this->get_entity()->has($key) ?? null;
	}

	/**
	 * @param string  $method_name
	 * @param mixed[] ...$arguments
	 * @return Entity|Entity[]
	 * @throws ReflectionException
	 * @throws Exception
	 */
	protected function select($method_name, ...$arguments) {
		$fields = explode('_from_', $method_name)[0];
		$fields = str_replace(['get_', 'select_', 'find_'], '', $fields);
		$fields = explode('_', $fields);

		$from_keys = explode('_from_', $method_name)[1];
		$from_keys = explode('_', $from_keys);
		$from_keys = $this->associate_keys_and_values($from_keys, $arguments);
		return count($fields) === 1 && $fields[0] === 'all'
			? $this->get_from(array_keys($from_keys), array_values($from_keys))
			: $this->get_keys_from($fields, array_keys($from_keys), array_values($from_keys));
	}

	/**
	 * @param string $method_name
	 * @param mixed  ...$arguments
	 * @return array|bool|Mysql
	 * @throws Exception
	 */
	protected function delete($method_name, ...$arguments) {
		$mysql = $this->get_mysql();
		$fields = explode('_where_', $method_name)[1];
		$fields = explode('_', $fields);
		$fields = $this->associate_keys_and_values($fields, $arguments);
		return $mysql->query((empty($arguments) ?
			'DELETE FROM `'.$this->get_table().'`'
			: 'DELETE FROM `'.$this->get_table().'` WHERE '.implode(' AND ', $this->get_sql_string_from_array($fields))));
	}

	/**
	 * @param string $method_name
	 * @param mixed ...$arguments
	 * @throws Exception
	 */
	protected function update($method_name, ...$arguments) {
		throw new Exception('Le type de requête `UPDATE` n\'à pas encore été développé !');
	}

	/**
	 * @param string $method_name
	 * @param mixed  ...$arguments
	 * @return Entity|bool
	 * @throws Exception
	 */
	protected function insert($method_name, ...$arguments) {
		$method_name = str_replace(['set_', 'insert_'], '', $method_name);
		$fields = explode('_', $method_name);
		$fields = $this->associate_keys_and_values($fields, $arguments);
		$mysql = $this->get_mysql();
		$fields = array_keys($fields);
		$arg_str = [];
		foreach ($arguments as $argument) {
			$arg_str[] = is_string($argument) ? '"'.$argument.'"' : (is_bool($argument) ? ($argument ? '1' : '0') : $argument);
		}
		$insert = $mysql->query('INSERT INTO `'.$this->get_table().'` (`'.implode('`, `', $fields).'`) VALUES('.implode(', ', $arg_str).')');
		if(!$insert) {
			return false;
		}
		$mysql->query('SELECT id FROM `'.$this->get_table().'` ORDER BY id DESC LIMIT 1');
		list($insert_id) = $mysql->fetch_row();
		$mysql->query('SELECT * FROM `'.$this->get_table().'` WHERE `id`='.$insert_id);
		return $mysql->fetch_object($this->get_entity_dependency_name());
	}

	/**
	 * @param string $method
	 * @return bool
	 */
	protected final function is_select($method) {
		return substr($method, 0, strlen('get_')) === 'get_'
			   || substr($method, 0, strlen('select_')) === 'select_'
			   || substr($method, 0, strlen('find_')) === 'find_';
	}

	/**
	 * @param string $method
	 * @return bool
	 */
	protected final function is_delete($method) {
		return substr($method, 0, strlen('del_')) === 'del_'
			   || substr($method, 0, strlen('delete_')) === 'delete_'
			   || substr($method, 0, strlen('remove_')) === 'remove_';
	}

	/**
	 * @param string $method
	 * @return bool
	 */
	protected final function is_update($method) {
		return substr($method, 0, strlen('update_')) === 'update_';
	}

	/**
	 * @param string $method
	 * @return bool
	 */
	protected final function is_insert($method) {
		return substr($method, 0, strlen('insert_')) === 'insert_'
		|| substr($method, 0, strlen('set_')) === 'set_';
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	protected function get_table() {
		return $this->get_entity()->get_table();
	}

	/**
	 * @return string|null
	 */
	protected final function get_entity_dependency_name() {
		if($this->entity_class && Dependency::is_in($this->entity_class)) {
			return Dependency::get_name_from_class($this->entity_class);
		}
		return null;
	}

	/**
	 * @return Entity
	 * @throws Exception
	 */
	public function get_entity() {
		$dependency_name = $this->get_entity_dependency_name();
		if($dependency_name) return $this->inject->{'get_'.$dependency_name}();
		throw new Exception($this->inject->get_service_translation()->__("L'entité %1 n'à pas été reconnu !", [$this->entity_class]));
	}

	/**
	 * @return Entity[]|Entity
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
		$current_method = $this->has_virtual_method($name);
		if(!$current_method) {
			return parent::call($name, ...$arguments);
		}
		$method_name = $current_method['name'];
		$method_params = $current_method['params_type'];
		if(count($arguments) < count($method_params)) {
			throw new Exception($this->inject->get_service_translation()
											 ->__(get_class($this).'::'.$method_name.'() requis '.count($method_params).' et vous en avez renseigné '.count($arguments)));
		}
		if($this->is_select($name)) return $this->select($method_name, ...$arguments);
		elseif ($this->is_delete($name)) return $this->delete($name, ...$arguments);
		elseif ($this->is_update($name)) return $this->update($name, ...$arguments);
		elseif ($this->is_insert($name)) return $this->insert($name, ...$arguments);
		return null;
	}
}