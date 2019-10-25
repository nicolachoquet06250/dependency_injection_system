<?php
	
	
	namespace mvc_router\services;
	
	
	use mvc_router\interfaces\Encoder;
	
	/**
	 * Simple parseur Yaml
	 *
	 * @package mvc_router\services
	 */
	class Yaml extends Service implements Encoder {
		
		/**
		 * @inheritDoc
		 */
		public function encode( $object ): string {
			return \Symfony\Component\Yaml\Yaml::dump($object);
		}
		
		/**
		 * @inheritDoc
		 */
		public function decode( $string ): array {
			return \Symfony\Component\Yaml\Yaml::parse($string);
		}
		
		/**
		 * @param $path
		 * @return mixed
		 */
		public function decode_from_file($path) {
			return \Symfony\Component\Yaml\Yaml::parseFile($path);
		}
		
		/**
		 * @param $url
		 * @return mixed
		 */
		public function decode_from_url($url) {
			$url_content = file_get_contents($url);
			return self::decode($url_content);
		}
		
		/**
		 * @param $path
		 * @param $object
		 * @return mixed
		 */
		public function encode_to_file($path, $object) {
			$path_content = file_get_contents($path);
			return \Symfony\Component\Yaml\Yaml::dump($path_content);
		}
	}