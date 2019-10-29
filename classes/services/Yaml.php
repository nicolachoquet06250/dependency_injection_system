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
			if(class_exists('\Symfony\Component\Yaml\Yaml')) {
				return \Symfony\Component\Yaml\Yaml::dump( $object );
			}
			return '';
		}
		
		/**
		 * @inheritDoc
		 */
		public function decode( $string ): array {
			if(class_exists('\Symfony\Component\Yaml\Yaml')) {
				return \Symfony\Component\Yaml\Yaml::parse( $string );
			}
			return [];
		}
		
		/**
		 * @param $path
		 * @return mixed
		 */
		public function decode_from_file($path) {
			if(class_exists('\Symfony\Component\Yaml\Yaml')) {
				return \Symfony\Component\Yaml\Yaml::parseFile( $path );
			}
			return [];
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
		 * @return string
		 */
		public function encode_to_file($path, $object) {
			$path_content = file_get_contents($path);
			if(class_exists('\Symfony\Component\Yaml\Yaml')) {
				return \Symfony\Component\Yaml\Yaml::dump( $path_content );
			}
			return '';
		}
	}