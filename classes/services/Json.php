<?php

namespace mvc_router\services;

use mvc_router\interfaces\Encoder;

/**
 * Permet d'encoder ou décoder un contenu JSON
 *
 * @package mvc_router\services
 */
class Json extends Service implements Encoder {
	/**
	 * @inheritDoc
	 */
	public function encode($object): string {
		return json_encode($object);
	}
	
	/**
	 * @inheritDoc
	 */
	public function decode($string, $assoc = false): array {
		return json_decode($string, $assoc);
	}
}