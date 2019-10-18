<?php
	
	
	namespace mvc_router\interfaces;
	
	
	interface Encoder {
		/**
		 * @param $object
		 * @return string
		 */
		public function encode($object): string;
		
		/**
		 * @param $string
		 * @return array
		 */
		public function decode($string): array;
	}