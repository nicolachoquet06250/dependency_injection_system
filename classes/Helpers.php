<?php


namespace mvc_router\helpers;


use mvc_router\Base;
use mvc_router\interfaces\Singleton;

class Helpers extends Base implements Singleton {
	private static $instance = null;

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new Helpers();
		}
		return self::$instance;
	}

	public function array_flatten($array, $preserve_keys = 1, &$newArray = []) {
		foreach ($array as $key => $child) {
			if (is_array($child)) {
				$newArray = $this->array_flatten($child, $preserve_keys, $newArray);
			} elseif ($preserve_keys + is_string($key) > 1) {
				$newArray[$key] = $child;
			} else {
				$newArray[] = $child;
			}
		}
		return $newArray;
	}
}