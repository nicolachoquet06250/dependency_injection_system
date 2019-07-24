<?php

namespace mvc_router\services;

class Json extends Service {
	public function encode($object) {
		return json_encode($object);
	}

	public function decode($string) {
		return json_decode($string);
	}
}