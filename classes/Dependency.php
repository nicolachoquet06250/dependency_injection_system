<?php


namespace mvc_router\dependencies;


use Exception;
use mvc_router\Base;
use mvc_router\confs\Conf;
use mvc_router\data\gesture\Manager;
use mvc_router\WrapperFactory;
use ReflectionClass;
use ReflectionException;

class Dependency {
	const SINGLETON = true;
	const FACTORY = false;
	const NONE = null;

	const CONTROLLER = 'controller';
	const MODEL      = 'model';
	const VIEW       = 'view';
	const MANAGER    = 'manager';
	const ENTITY     = 'entity';
	const SERVICE    = 'service';
	const ROUTER     = 'router';
	const HELPERS    = 'helpers';
	const COMMANDS   = 'commands';
	const COMMAND    = 'command';

	const ROUTES_CONTROLLER       = 'routes_controller';
	const ROUTE_VIEW              = 'route_view';
	const JSON_SERVICE            = 'service_json';
	const ERROR_SERVICE           = 'service_error';
	const LOGGER_SERVICE          = 'service_logger';
	const ROUTE_SERVICE           = 'service_route';
	const FILE_GENERATION_SERVICE = 'service_generation';
	const SESSION_SERVICE         = 'service_session';
	const TRANSLATION_SERVICE     = 'service_translation';
	const FILE_SYSTEM_SERVICE     = 'service_fs';
	const TRIGGER_SERVICE         = 'service_trigger';
	const LOCK_SERVICE            = 'service_lock';
	const URL_GENERATOR_SERVICE   = 'url_generator';

	const PHP_DOC_PARSER = 'phpdoc_parser';
	const QUEUE          = 'util_queue';
	const QUEUE_LIST     = 'util_queue_list';

	const HELP_COMMAND     = 'command_help';
	const TEST_COMMAND     = 'command_test';
	const GENERATE_COMMAND = 'command_generate';
	const CLONE_COMMAND    = 'command_clone';
	const INSTALL_COMMAND  = 'command_install';
	const START_COMMAND    = 'command_start';

	const CLASS_TRIGGERS = 'triggers';
	const CLASS_REQUEST  = 'request';

	protected static $base_dependencies = [
		__DIR__.'/interfaces/Singleton.php',
		__DIR__.'/WrapperFactory.php',
		__DIR__.'/Base.php',
	];

	private static $dependencies = [
		'mvc_router\mvc\Controller' 			=> [
			'name'         => self::CONTROLLER,
			'file'         => __DIR__.'/mvc/Controller.php',
			'is_singleton' => false,
		],
		'mvc_router\mvc\Routes'     			=> [
			'name'         => self::ROUTES_CONTROLLER,
			'file'         => __DIR__.'/mvc/controllers/Routes.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\mvc\Controller'
		],
		'mvc_router\mvc\Model'      			=> [
			'name'         => self::MODEL,
			'file'         => __DIR__.'/mvc/Model.php',
			'is_singleton' => false,
		],
		'mvc_router\mvc\View'       			=> [
			'name'         => self::VIEW,
			'file'         => __DIR__.'/mvc/View.php',
			'is_singleton' => false
		],

		'mvc_router\mvc\views\Route' 			=> [
			'name'         => self::ROUTE_VIEW,
			'file'         => __DIR__.'/mvc/views/Route.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\mvc\View'
		],

		'mvc_router\data\gesture\Manager' 		=> [
			'name'         => self::MANAGER,
			'file'         => __DIR__.'/data_gesture/Manager.php',
			'is_singleton' => false,
		],
		'mvc_router\data\gesture\Entity'  		=> [
			'name'         => self::ENTITY,
			'file'         => __DIR__.'/data_gesture/Entity.php',
			'is_singleton' => false,
		],

		'mvc_router\services\Service'           => [
			'name'         => self::SERVICE,
			'file'         => __DIR__.'/utils/services/Service.php',
			'is_singleton' => false,
		],
		'mvc_router\services\Json'              => [
			'name'         => self::JSON_SERVICE,
			'file'         => __DIR__.'/services/Json.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\Error'             => [
			'name'         => self::ERROR_SERVICE,
			'file'         => __DIR__.'/services/Error.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\Logger'            => [
			'name'         => self::LOGGER_SERVICE,
			'file'         => __DIR__.'/services/Logger.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\Route'             => [
			'name'         => self::ROUTE_SERVICE,
			'file'         => __DIR__.'/services/Route.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service'
		],
		'mvc_router\services\FileGeneration'    => [
			'name'         => self::FILE_GENERATION_SERVICE,
			'file'         => __DIR__.'/services/FileGeneration.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\Session'           => [
			'name'         => self::SESSION_SERVICE,
			'file'         => __DIR__.'/services/Session.php',
			'is_singleton' => true,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\Translate'         => [
			'name'         => self::TRANSLATION_SERVICE,
			'file'         => __DIR__.'/services/Translate.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\FileSystem'        => [
			'name'         => self::FILE_SYSTEM_SERVICE,
			'file'         => __DIR__.'/services/FileSystem.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\Trigger'           => [
			'name'         => self::TRIGGER_SERVICE,
			'file'         => __DIR__.'/services/Trigger.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\TriggerRegisterer' => [
			'name'         => self::CLASS_TRIGGERS,
			'file'         => __DIR__.'/services/TriggerRegisterer.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\Lock'              => [
			'name'         => self::LOCK_SERVICE,
			'file'         => __DIR__.'/services/Lock.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],
		'mvc_router\services\UrlGenerator'      => [
			'name'         => self::URL_GENERATOR_SERVICE,
			'file'         => __DIR__.'/services/UrlGenerator.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\services\Service',
		],

		'mvc_router\parser\PHPDocParser' 		=> [
			'name'         => self::PHP_DOC_PARSER,
			'file'         => __DIR__.'/utils/parsers/PHPDocParser.php',
			'is_singleton' => true
		],
		'mvc_router\queues\Queue'        		=> [
			'name'         => self::QUEUE,
			'file'         => __DIR__.'/utils/queues/Queue.php',
			'is_singleton' => false
		],
		'mvc_router\queues\QueueList'    		=> [
			'name'         => self::QUEUE_LIST,
			'file'         => __DIR__.'/utils/queues/QueueList.php',
			'is_singleton' => true
		],

		'mvc_router\router\Router' 				=> [
			'name'         => self::ROUTER,
			'file'         => __DIR__.'/mvc/Router.php',
			'is_singleton' => true,
		],

		'mvc_router\helpers\Helpers' 			=> [
			'name'         => self::HELPERS,
			'file'         => __DIR__.'/utils/Helpers.php',
			'is_singleton' => true,
		],

		'mvc_router\commands\Commands'        	=> [
			'name'         => self::COMMANDS,
			'file'         => __DIR__.'/utils/commands/Commands.php',
			'is_singleton' => true,
		],
		'mvc_router\commands\Command'         	=> [
			'name'         => self::COMMAND,
			'file'         => __DIR__.'/utils/commands/Command.php',
			'is_singleton' => false,
		],
		'mvc_router\commands\HelpCommand'  		=> [
			'name'         => self::HELP_COMMAND,
			'file'         => __DIR__.'/commands/HelpCommand.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\commands\Command',
		],
		'mvc_router\commands\TestCommand'     	=> [
			'name'         => self::TEST_COMMAND,
			'file'         => __DIR__.'/commands/TestCommand.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\commands\Command',
		],
		'mvc_router\commands\GenerateCommand' 	=> [
			'name'         => self::GENERATE_COMMAND,
			'file'         => __DIR__.'/commands/GenerateCommand.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\commands\Command',
		],
		'mvc_router\commands\CloneCommand'    	=> [
			'name'         => self::CLONE_COMMAND,
			'file'         => __DIR__.'/commands/CloneCommand.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\commands\Command',
		],
		'mvc_router\commands\InstallCommand'  	=> [
			'name'         => self::INSTALL_COMMAND,
			'file'         => __DIR__.'/commands/InstallCommand.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\commands\Command',
		],
		'mvc_router\commands\StartCommand'  	=> [
			'name'         => self::START_COMMAND,
			'file'         => __DIR__.'/commands/StartCommand.php',
			'is_singleton' => false,
			'parent'       => 'mvc_router\commands\Command',
		],

		'Curl\Curl' 							=> [
			'name'         => self::CLASS_REQUEST,
			'file'         => __DIR__.'/../vendor/autoload.php',
			'is_singleton' => false,
		],
	];

	public static function get_dependencies() {
		return self::$dependencies;
	}

	public static function load_base_dependencies() {
		foreach (self::$base_dependencies as $base_dependency) {
			if(is_file($base_dependency)) require_once $base_dependency;
		}
	}

	/**
	 * @return WrapperFactory|null
	 */
	public static function get_wrapper_factory() {
		return WrapperFactory::create();
	}

	/**
	 * @param string $class
	 * @return bool
	 */
	public static function is_in($class) {
		if(substr($class, 0, 1) === '\\') {
			$class = substr($class, 1, strlen($class) - 1);
		}
		return isset(self::$dependencies[$class]);
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
						require_once realpath(self::$dependencies[$parent]['file']);
					}
				}
				require_once realpath(self::$dependencies[$classname]['file']);
			}
			$instanciate_method = isset(self::$confs[$classname]['method']) ? self::$confs[$classname]['method'] : 'create';
			return (isset(self::$dependencies[$classname]['is_singleton']) && self::$dependencies[$classname]['is_singleton']) ||
				   (isset(self::$dependencies[$classname]['is_factory']) && self::$dependencies[$classname]['is_factory'])
				? $classname::$instanciate_method() : new $classname();
		}
		else throw new Exception($classname.' is not a dependency');
	}

	/**
	 * @param $classname
	 * @return mixed|null
	 */
	public static function get_name_from_class($classname) {
		if(substr($classname, 0, 1) === '\\') {
			$classname = substr($classname, 1, strlen($classname) - 1);
		}
		if(isset(self::$dependencies[$classname])) {
			return self::$dependencies[$classname]['name'];
		}
		return null;
	}

	/**
	 * @param string $name
	 * @return string|null
	 */
	public static function get_class_from_name($name) {
		foreach (self::$dependencies as $dependency_class => $dependency) {
			if($dependency['name'] === $name) {
				return $dependency_class;
			}
		}
		return null;
	}

	/**
	 * @param string $name
	 * @return Base
	 * @throws Exception
	 */
	public static function get_from_name($name) {
		return self::get_from_classname(self::get_class_from_name($name));
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

	/**
	 * @return bool
	 */
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

	/**
	 * @param      $class
	 * @param      $name
	 * @param      $file
	 * @param mixed $parent
	 * @param mixed $type
	 */
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

	public static function add_custom_dependencies(...$dependencies) {
		foreach ($dependencies as $dependency) {
			self::add_custom_dependency(
				$dependency['class'],
				$dependency['name'],
				$dependency['file'],
				(isset($dependency['parent']) ? $dependency['parent'] : null),
				(isset($dependency['type']) ? $dependency['type'] : self::NONE)
			);
		}
	}

	public static function extend_dependency($old_class, $new_class, $details) {
		$type = isset($details['type']) ? $details['type'] : self::NONE;
		self::$dependencies[$old_class]['name'] = '_'.self::$dependencies[$old_class]['name'];
		$parent = isset($details['parent']) ? $details['parent'] : $old_class;
		self::add_custom_dependency($new_class, $details['name'], $details['file'], $parent, $type);
	}

	public static function extend_dependencies(...$dependencies) {
		foreach ($dependencies as $dependency) {
			$class = $dependency['class'];
			unset($dependency['class']);
			$details = [
				'name' => $dependency['name'],
				'file' => $dependency['file'],
			];
			$details['type'] = isset($dependency['type']) ? $dependency['type'] : self::NONE;
			if($dependency['parent']) {
				$details['parent'] = $dependency['parent'];
			}
			self::extend_dependency(
				$class['old'],
				$class['new'],
				$details
			);
		}
	}

	/**
	 * @param string 		$class
	 * @param string 		$name
	 * @param string 		$file
	 * @param string|null	$type
	 */
	public static function add_custom_controller($class, $name, $file, $type = self::NONE) {
		self::add_custom_dependency($class, $name, $file, '\mvc_router\mvc\Controller', $type);
	}

	/**
	 * @param array ...$controller
	 */
	public static function add_custom_controllers(...$controller) {
		foreach ($controller as $ctrl_details) {
			$type = isset($ctrl_details['type']) ? $ctrl_details['type'] : self::NONE;
			self::add_custom_controller($ctrl_details['class'], $ctrl_details['name'], $ctrl_details['file'], $type);
		}
		self::require_dependency_wrapper();
	}

	/**
	 * @param      $class
	 * @param      $name
	 * @param      $file
	 * @param null $type
	 */
	public static function add_custom_command($class, $name, $file, $type = self::NONE) {
		self::add_custom_dependency($class, $name, $file, '\mvc_router\commands\Command', $type);
	}

	/**
	 * @param array $command
	 */
	public static function add_custom_commands(...$command) {
		foreach ($command as $cmd_details) {
			$type = isset($ctrl_details['type']) ? $cmd_details['type'] : self::NONE;
			self::add_custom_command($cmd_details['class'], $cmd_details['name'], $cmd_details['file'], $type);
		}
		self::require_dependency_wrapper();
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
	 * @return array
	 */
	public static function controllers() {
		$controllers = [];
		foreach (self::$dependencies as $class => $dependency) {
			if(self::is_controller($dependency['name'])) {
				$controllers[$class] = $dependency;
			}
		}
		return $controllers;
	}

	/**
	 * @return array
	 */
	public static function commands() {
		$commands = [];
		foreach (self::$dependencies as $class => $dependency) {
			if(self::is_command($dependency['name'])) {
				$commands[$class] = $dependency;
			}
		}
		return $commands;
	}

	/**
	 * @param $method
	 * @return bool
	 * @throws ReflectionException
	 */
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

	/**
	 * @param $elem
	 * @return bool
	 */
	public static function exists($elem) {
		foreach (self::$dependencies as $dependency) {
			if($dependency['name'] === $elem) return true;
		}
		return false;
	}

	/**
	 * @param $elem
	 * @return bool
	 */
	public static function is_controller($elem) {
		foreach (self::$dependencies as $class => $dependency) {
			if($dependency['name'] === $elem) {
				if(isset($dependency['parent']) && ($dependency['parent'] === 'mvc_router\mvc\Controller' || $dependency['parent'] === '\mvc_router\mvc\Controller')) {
					return true;
				}
				if(!isset($dependency['parent']) && ($class === 'mvc_router\mvc\Controller' || $class === '\mvc_router\mvc\Controller')) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param string $elem
	 * @return bool
	 */
	public static function is_command($elem) {
		foreach (self::$dependencies as $class => $dependency) {
			if($dependency['name'] === $elem) {
				if(isset($dependency['parent']) && ($dependency['parent'] === 'mvc_router\commands\Command' || $dependency['parent'] === '\mvc_router\commands\Command')) {
					return true;
				}
				if(!isset($dependency['parent']) && ($class === 'mvc_router\commands\Command' || $class === '\mvc_router\commands\Command')) {
					return true;
				}
			}
		}
		return false;
	}

	public static function is_view($elem) {
		foreach (self::$dependencies as $class => $dependency) {
			if($dependency['name'] === $elem) {
				if(isset($dependency['parent']) && ($dependency['parent'] === 'mvc_router\mvc\View' || $dependency['parent'] === '\mvc_router\mvc\View')) {
					return true;
				}
				if(!isset($dependency['parent']) && ($class === 'mvc_router\mvc\View' || $class === '\mvc_router\mvc\View')) {
					return true;
				}
			}
		}
		return false;
	}

	public static function is_composer($elem) {
		foreach (self::$dependencies as $class => $dependency) {
			if($dependency['name'] === $elem) {
				if(strstr($dependency['file'], 'vendor/autoload.php')) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param $method
	 * @return string|null
	 * @throws ReflectionException
	 */
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

	/**
	 * @param $class
	 * @throws Exception
	 */
	public static function autoload($class) {
		if(Dependency::is_in($class)) {
			Dependency::get_from_classname($class);
		}
		elseif (Conf::is_in($class)) {
			Conf::get_from_classname($class);
		}
	}

	/**
	 * FOR ERROR HANDLER
	 *
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @return string
	 */
	private static function format_error( $errno, $errstr, $errfile, $errline ) {
		$trace = print_r( debug_backtrace( false ), true );
		$content = "<b style='color: red;'>Error( $errstr; code( $errno ); file( $errfile ); line( $errline ); <pre>$trace</pre> )</b>";
		return $content;
	}

	/**
	 * FOR ERROR HANDLER
	 */
	public static function fatal_handler() {
		$error = error_get_last();

		if(!is_null($error)) {
			$errno   = $error["type"];
			$errfile = $error["file"];
			$errline = $error["line"];
			$errstr  = $error["message"];

			echo self::format_error( $errno, $errstr, $errfile, $errline);
		}
	}

	/**
	 * @return Manager[]
	 */
	public static function get_managers() {
		$managers_class = [];
		foreach (self::$dependencies as $dependency) {
			if(isset($dependency['parent']) && $dependency['parent'] === 'mvc_router\data\gesture\Manager') {
				$managers_class[str_replace([__SITE_NAME__.'_', '_manager'], '', $dependency['name'])] = self::get_wrapper_factory()->get_dependency_wrapper()->{"get_{$dependency['name']}"}();
			}
		}
		return $managers_class;
	}
}
