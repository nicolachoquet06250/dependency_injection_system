<?php


namespace mvc_router\data\gesture;


use mvc_router\Base;

class Entity extends Base {
	public function get($key) {
		return $this->$key;
	}
	public function set($key, $value) {
		$this->$key = $value;
		return $this;
	}
}