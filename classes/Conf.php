<?php


namespace mvc_router\confs;


use Exception;
use mvc_router\Base;
use ReflectionClass;
use ReflectionException;

class Conf {
	const SINGLETON = true;
	const FACTORY = false;
	const NONE = null;

	protected static $confs = [
		'mvc_router\confs\Mysql' => [
			'name' => 'mysql',
			'file' => __DIR__.'/confs/Mysql.php',
			'is_singleton' => false
		]
	];

	/**
	 * @param string $class
	 * @return bool
	 */
	public static function is_in($class) {
		if(substr($class, 0, 1) === '\\') {
			$class = substr($class, 1, strlen($class) - 1);
		}
		return isset(self::$confs[$class]);
	}

	/**
	 * @return bool
	 */
	private static function conf_wrapper_exists() {
		return file_exists(__DIR__.'/ConfWrapper.php');
	}

	private static function generate_conf_wrapper() {
		$class_start = "<?php
		
\tnamespace mvc_router\confs;
\t/**
\t * Get all methods for conf injection
\t *
";
		$class_end = "\t **/
\tclass ConfWrapper extends Conf {}
";

		$final_class = $class_start;
		foreach (self::$confs as $conf_class => $conf_details) {
			if(substr($conf_class, 0, 1) !== '\\') {
				$conf_class = '\\'.$conf_class;
			}
			$final_class .= "\t * @method ".$conf_class." get_".$conf_details['name']."()\n";
		}
		$final_class .= $class_end;

		file_put_contents(__DIR__.'/ConfWrapper.php', $final_class);
	}

	public static function require_conf_wrapper() {
		if(!self::conf_wrapper_exists()) {
			self::generate_conf_wrapper();
		}
		require_once __DIR__.'/ConfWrapper.php';
	}

	/**
	 * @param      $class
	 * @param      $name
	 * @param      $file
	 * @param mixed $parent
	 * @param mixed $type
	 */
	public static function add_custom_conf($class, $name, $file, $parent = null, $type = self::NONE) {
		if(substr($class, 0, 1) === '\\') {
			$class = substr($class, 1, strlen($class) - 1);
		}
		if(is_null($type)) {
			Conf::$confs[$class] = [
				'name' => $name,
				'file' => $file,
				'is_singleton' => false,
			];
		}
		else {
			Conf::$confs[$class] = [
				'name' => $name,
				'file' => $file,
				($type === self::SINGLETON ? 'is_singleton' : 'is_factory') => true,
			];
		}
		if(!is_null($parent)) {
			Conf::$confs[$class]['parent'] = $parent;
		}
		if(!is_file(__DIR__.'/ConfWrapper.php')
		   || (is_file(__DIR__.'/ConfWrapper.php')
			   && !strstr('get_'.$name, file_get_contents(__DIR__.'/ConfWrapper.php')))) {
			self::delete_conf_wrapper();
			self::require_conf_wrapper();
		}
	}

	public static function extend_conf($old_class, $new_class, $details) {
		self::$confs[$old_class]['name'] = '_'.self::$confs[$old_class]['name'];
		self::add_custom_conf($new_class, $details['name'], $details['file'], $old_class, $details['type']);
	}

	public static function delete_conf_wrapper() {
		if(is_file(__DIR__.'/ConfWrapper.php')) {
			unlink(__DIR__.'/ConfWrapper.php');
		}
	}

	/**
	 * @param $method
	 * @return bool
	 * @throws ReflectionException
	 */
	private static function method_exists($method) {
		$rc = new ReflectionClass(ConfWrapper::class);
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
				return self::get_from_classname(self::get_class_from_method('get_'.$name));
			}
			return null;
		}
		return null;
	}

	/**
	 * @param $method
	 * @return string|null
	 * @throws ReflectionException
	 */
	private static function get_class_from_method($method) {
		$rc = new ReflectionClass(ConfWrapper::class);
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

	/**
	 * @param $classname
	 * @return mixed|null
	 */
	public static function get_name_from_class($classname) {
		if(substr($classname, 0, 1) === '\\') {
			$classname = substr($classname, 1, strlen($classname) - 1);
		}
		if(isset(self::$confs[$classname])) {
			return self::$confs[$classname]['name'];
		}
		return null;
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
		if(isset(self::$confs[$classname])) {
			if(!class_exists($classname) || !in_array(self::$confs[$classname]['file'], get_required_files())) {
				if(isset(self::$confs[$classname]['parent'])) {
					$parent = self::$confs[$classname]['parent'];
					if(substr($parent, 0, 1) === '\\') {
						$parent = substr($parent, 1, strlen($parent) - 1);
					}
					if(isset(self::$confs[$parent])) {
						require_once self::$confs[$parent]['file'];
					}
				}
				require_once self::$confs[$classname]['file'];
			}
			return (isset(self::$confs[$classname]['is_singleton']) && self::$confs[$classname]['is_singleton']) ||
				   (isset(self::$confs[$classname]['is_factory']) && self::$confs[$classname]['is_factory'])
				? $classname::create() : new $classname();
		}
		else throw new Exception($classname.' is not a conf');
	}
}