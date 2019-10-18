<?php
	
	
	namespace mvc_router\parser;
	
	
	use mvc_router\Base;
	
	class YamlParser extends Base {
		protected $properties = [];
		
		public function __get($name) {
			if($name === 'properties') {
				return $this->properties;
			}
			return $this->properties[$name];
		}
		
		public function __set($name, $arguments) {
			if($name === 'properties') {
				$this->properties = $arguments;
			}
			$this->properties[$name] = $arguments;
		}
		
		public function parseFile($path) {
			if(realpath($path)) {
				return $this->parse( file_get_contents( $path ) );
			}
			return null;
		}
		
		protected function resetProperties() {
			$this->properties = [];
		}
		
		public function parse($yaml) {
			$this->resetProperties();
			foreach($this->inject->get_service_yaml()->decode($yaml) as $key => $value) {
				$this->$key = $value;
			}
			return $this;
		}
	}