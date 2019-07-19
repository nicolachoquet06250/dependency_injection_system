<?php


namespace mvc_router\dependencies;



use Exception;
use mvc_router\Base;
use mvc_router\WrapperFactory;
use ReflectionClass;

class Dependency {
	const SINGLETON = true;
	const FACTORY = false;
	const NONE = null;

	protected static $base_dependencies = [
		__DIR__.'/../interfaces/Singleton.php',
		__DIR__.'/../classes/WrapperFactory.php',
		__DIR__.'/../classes/Base.php',
	];

	private static $dependencies = [
		'mvc_router\mvc\Controller' => [
			'name' => 'controller',
			'file' => __DIR__.'/Controller.php',
			'is_singleton' => false
		],
		'mvc_router\router\RouteFlag' => [
			'name' => 'route_flag',
			'file' => __DIR__.'/RouteFlag.php',
			'is_factory' => true
		],
		'mvc_router\router\Router' => [
			'name' => 'router',
			'file' => __DIR__.'/Router.php',
			'is_singleton' => true
		]
	];

	public static function load_base_dependencies() {
		foreach (self::$base_dependencies as $base_dependency) {
			if(is_file($base_dependency)) require_once $base_dependency;
		}
	}

	public static function get_wrapper_factory() {
		return WrapperFactory::create();
	}

	/**
	 * @param $classname
	 * @return Base
	 * @throws Exception
	 */
	public static function get_from_classname($classname) {
		if(gettype($classname) === 'object' && get_class($classname) === 'ReflectionClass') {
			$classname = $classname->getName();
		}
		if(substr($classname, 0, 1) === '\\') {
			$classname = substr($classname, 1, strlen($classname) - 1);
		}
		if(isset(self::$dependencies[$classname])) {
			if(!class_exists($classname)) {
				if(isset(self::$dependencies[$classname]['parent'])) {
					$parent = self::$dependencies[$classname]['parent'];
					if(substr($parent, 0, 1) === '\\') {
						$parent = substr($parent, 1, strlen($parent) - 1);
					}
					if(isset(self::$dependencies[$parent])) {
						require_once self::$dependencies[$parent]['file'];
					}
				}
				require_once self::$dependencies[$classname]['file'];
			}
			return (isset(self::$dependencies[$classname]['is_singleton']) && self::$dependencies[$classname]['is_singleton']) ||
				   (isset(self::$dependencies[$classname]['is_factory']) && self::$dependencies[$classname]['is_factory'])
				? $classname::create() : new $classname();
		}
		else throw new Exception($classname.' is not a dependency');
	}

	private static function generate_dependency_wrapper() {
		$class_start = "<?php
		
\tnamespace mvc_router\dependencies;
\t/**
\t * Get all methods for dependency injection
\t *
";
		$class_end = "\t **/
\tclass DependencyWrapper extends Dependency {}
";

		$final_class = $class_start;
		foreach (self::$dependencies as $dependency_class => $dependency_details) {
			if(substr($dependency_class, 0, 1) !== '\\') {
				$dependency_class = '\\'.$dependency_class;
			}
			$final_class .= "\t * @method ".$dependency_class." get_".$dependency_details['name']."()\n";
		}
		$final_class .= $class_end;

		file_put_contents(__DIR__.'/DependencyWrapper.php', $final_class);
	}

	private static function dependency_wrapper_exists() {
		return file_exists(__DIR__.'/DependencyWrapper.php');
	}

	private static function delete_dependency_wrapper() {
		unlink(__DIR__.'/DependencyWrapper.php');
	}

	public static function require_dependency_wrapper() {
		if(!self::dependency_wrapper_exists()) {
			self::generate_dependency_wrapper();
		}
		require_once __DIR__.'/DependencyWrapper.php';
	}

	public static function add_custom_dependency($class, $name, $file, $parent = null, $type = self::NONE) {
		if(substr($class, 0, 1) === '\\') {
			$class = substr($class, 1, strlen($class) - 1);
		}
		if(is_null($type)) {
			Dependency::$dependencies[$class] = [
				'name' => $name,
				'file' => $file,
				'is_singleton' => false,
			];
		}
		else {
			Dependency::$dependencies[$class] = [
				'name' => $name,
				'file' => $file,
				($type === self::SINGLETON ? 'is_singleton' : 'is_factory') => true,
			];
		}
		if(!is_null($parent)) {
			Dependency::$dependencies[$class]['parent'] = $parent;
		}
		if(!is_file(__DIR__.'/DependencyWrapper.php')
		   || (is_file(__DIR__.'/DependencyWrapper.php')
			   && !strstr('get_'.$name, file_get_contents(__DIR__.'/DependencyWrapper.php')))) {
			self::delete_dependency_wrapper();
			self::require_dependency_wrapper();
		}
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return null
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		if(substr($name, 0, 4) === 'get_') {
			$name = substr($name, 4, strlen($name) - 4);
			$dependency_class = null;
			if(self::method_exists('get_'.$name)) {
				return Dependency::get_from_classname(self::get_class_from_method('get_'.$name));
			}
			return null;
		}
		return null;
	}

	private static function method_exists($method) {
		$rc = new ReflectionClass(DependencyWrapper::class);
		$doc = $rc->getDocComment();
		$doc = str_replace(["\t", '/**', '**/', ' * ', ' *'], '', $doc);

		$doc = explode("\n", $doc);

		$doc_tmp = [];
		foreach ($doc as $line) {
			if($line !== '' && $line !== ' ' && substr($line, 0, 8) === '@method ') {
				$_line = str_replace(['@method ', '()'], '', $line);
				$method_name = explode(' ', $_line)[1];
				$doc_tmp[] = $method_name;
			}
		}
		$doc = $doc_tmp;

		return in_array($method, $doc);
	}

	public static function exists($elem) {
		foreach (self::$dependencies as $dependency) {
			if($dependency['name'] === $elem) return true;
		}
		return false;
	}

	public static function is_controller($elem) {
		foreach (self::$dependencies as $class => $dependency) {
			if($dependency['name'] === $elem) {
				if($dependency['parent'] === 'mvc_router\mvc\Controller' || $dependency['parent'] === '\mvc_router\mvc\Controller' || $class === 'mvc_router\mvc\Controller' || $class === '\mvc_router\mvc\Controller') return true;
			}
		}
		return false;
	}

	private static function get_class_from_method($method) {
		$rc = new ReflectionClass(DependencyWrapper::class);
		$doc = $rc->getDocComment();
		$doc = str_replace(["\t", '/**', '**/', ' * ', ' *'], '', $doc);

		$doc = explode("\n", $doc);

		foreach ($doc as $line) {
			if($line !== '' && $line !== ' ' && substr($line, 0, 8) === '@method ') {
				$_line = str_replace(['@method ', '()'], '', $line);
				$method_name = explode(' ', $_line);

				if($method_name[1] === $method) {
					return $method_name[0];
				}
			}
		}
		return null;
	}

	public static function autoload() {
		function __autoload($class) {
			Dependency::get_from_classname($class);
		}
	}
}