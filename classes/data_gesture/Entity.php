<?php


namespace mvc_router\data\gesture;


use mvc_router\Base;
use ReflectionException;
use ReflectionProperty;

class Entity extends Base {
	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->$key;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return $this|Base
	 * @throws ReflectionException
	 */
	public function set($key, $value) {
		$prop_ref = new ReflectionProperty($this->get_class(), $key);
		if(strstr($prop_ref->getDocComment(), 'int') || strstr($prop_ref->getDocComment(), 'integer')) {
			$value = (int)$value;
		} elseif (strstr($prop_ref->getDocComment(), 'bool') || strstr($prop_ref->getDocComment(), 'boolean')) {
			$value = (bool)$value;
		} elseif (strstr($prop_ref->getDocComment(), 'string')) {
			$value = (string)$value;
		}
		$this->$key = $value;
		return $this;
	}
}