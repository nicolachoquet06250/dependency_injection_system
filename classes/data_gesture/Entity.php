<?php


namespace mvc_router\data\gesture;


use Exception;
use mvc_router\Base;
use mvc_router\dependencies\Dependency;
use ReflectionClass;
use ReflectionException;

class Entity extends Base {

	protected $manager_class;
	protected $updated_fields = [];

	/**
	 * @return string
	 */
	protected function get_manager_class() {
		if($this->manager_class) {
			return $this->manager_class;
		}
		$class = $this->get_class();
		return str_replace('entities', 'managers', $class);
	}

	/**
	 * @return string
	 */
	public function get_table() {
		$class = get_class($this);
		$class = str_replace('\\', '/', $class);
		$class = explode('/', $class);
		$class = $class[count($class) - 1];
		preg_match_all('/[A-Z][a-z]+/', $class, $matches);
		$matches = $this->inject->get_helpers()->array_flatten($matches);
		$matches = array_map(function ($match) {
			return strtolower($match);
		}, $matches);
		return implode('_', $matches);
	}

	/**
	 * @return string|null
	 */
	protected final function get_entity_dependency_name() {
		if($this->get_manager_class() && Dependency::is_in($this->get_manager_class())) {
			return Dependency::get_name_from_class($this->get_manager_class());
		}
		return null;
	}

	/**
	 * @return Manager
	 * @throws Exception
	 */
	protected function get_manager() {
		$dependency_name = $this->get_entity_dependency_name();
		if($dependency_name) return $this->inject->{'get_'.$dependency_name}();
		throw new Exception($this->inject->get_service_translation()->__("Le manager %1 n'à pas été reconnu !", [$this->get_manager_class()]));
	}

	/**
	 * @return array
	 */
	protected final function get_table_fields() {
		$mysql = $this->confs->get_mysql();
		$mysql->query('SHOW COLUMNS FROM `'.$this->get_table().'`');
		$fields = $mysql->fetch_assoc();
		return array_map(function ($field_array) {
			return $field_array['Field'];
		}, $fields);
	}

	/**
	 * @param string $property
	 * @return bool|string
	 * @throws ReflectionException
	 */
	public function has($property) {
		$fields = $this->get_table_fields();
		$ref = new ReflectionClass($this->get_class());
		if ($ref->hasProperty($property)) {
			return $property;
		}
		foreach ($fields as $field) {
			$prop = implode('', explode('_', $field));
			if($property === $prop && $ref->hasProperty($field)) {
				return $field;
			}
		}
		return false;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @param bool   $init
	 * @return Base|Entity
	 * @throws ReflectionException
	 */
	public function set($key, $value, bool $init = false) {
		parent::set($key, $value);
		if(!$init) {
			$this->updated_fields[] = $key;
		}
		return $this;
	}

	/**
	 * @param string $property
	 * @return bool
	 */
	protected function is_property_updated($property) {
		return in_array($property, $this->updated_fields);
	}

	/**
	 * @return array|bool|\mvc_router\confs\Mysql
	 */
	public function save() {
		$mysql = $this->confs->get_mysql();
		$updated = [];
		foreach ($this->get_table_fields() as $table_field) {
			if($table_field !== 'id' && $this->is_property_updated($table_field)) {
				$updated[$table_field] = $this->get($table_field);
			}
		}

		foreach ($updated as $key => $value) {
			$updated[] = $key.'='.(is_string($value) ? '"'.$value.'"' : $value);
			unset($updated[$key]);
		}
		if(!empty($updated)) {
			$this->updated_fields = [];
			return $mysql->query('UPDATE `'.$this->get_table().'` SET '.implode(', ', $updated).' WHERE id='.$this->get('id'));
		}
		return true;
	}

	/**
	 * @return array
	 */
	public function to_json() {
		$fields = $this->get_table_fields();
		foreach ($fields as $i => $field) {
			$fields[$field] = $this->get($field);
			unset($fields[$i]);
		}
		return $fields;
	}

	/**
	 * @return array
	 */
	public function __debugInfo() {
		return $this->to_json();
	}
}