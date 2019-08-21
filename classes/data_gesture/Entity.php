<?php


namespace mvc_router\data\gesture;


use mvc_router\Base;
use ReflectionClass;
use ReflectionException;

class Entity extends Base {

	protected $updated_fields = [];

	/**
	 * @return string
	 */
	public function get_table() {
		$class = get_class($this);
		$class = str_replace('\\', '/', $class);
		$class = explode('/', $class);
		$class = $class[count($class) - 1];
		return strtolower($class);
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
	 * @return Base|void
	 * @throws ReflectionException
	 */
	public function set($key, $value, bool $init = false) {
		parent::set($key, $value);
		if(!$init) {
			$this->updated_fields[] = $key;
		}
	}

	protected function is_property_updated($property) {
		return in_array($property, $this->updated_fields);
	}

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
			return $mysql->query('UPDATE `'.$this->get_table().'` SET '.implode(', ', $updated).' WHERE id='.$this->get('id'));
		}
		return true;
	}
}