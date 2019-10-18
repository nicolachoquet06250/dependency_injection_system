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
	const VIEW       = 'view';
	const MANAGER    = 'manager';
	const ENTITY     = 'entity';
	const SERVICE    = 'service';
	const ROUTER     = 'router';
	const HELPERS    = 'helpers';
	const COMMANDS   = 'commands';
	const COMMAND    = 'command';
	const WEBSOCKET  = 'websocket';

	const ROUTES_CONTROLLER       = 'routes_controller';
	const ROUTE_VIEW              = 'route_view';
	const JSON_SERVICE            = 'service_json';
	const YAML_SERVICE            = 'service_yaml';
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
	const WEBSOCKET_SERVICE   	  = 'service_websocket';

	const PHP_DOC_PARSER = 'phpdoc_parser';
	const YAML_PARSER    = 'yaml';
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
	const RATCHET_APP_WS = 'ratchet_app_ws';
	
	const BASE_DEPENDENCY        = 0;
	const VIEW_DEPENDENCY        = 1;
	const CONTROLLER_DEPENDENCY  = 2;
	const DATA_MODELS_DEPENDENCY = 3;
	const SERVICE_DEPENDENCY     = 4;
	const HELPER_DEPENDENCY      = 5;
	const ROUTER_DEPENDENCY      = 6;
	const COMMAND_DEPENDENCY     = 7;
	const COMPOSER_DEPENDENCY    = 8;
	const WEBSOCKET_DEPENDENCY   = 9;
	
	const WEBSOCKET_DEFAULT_CONTROLLER = 'websocket_default_controller';

	protected static $base_dependencies = [
		__DIR__.'/interfaces/Singleton.php',
		__DIR__.'/interfaces/Encoder.php',
		__DIR__.'/WrapperFactory.php',
		__DIR__.'/Base.php',
	];

	private static $dependencies = [
		self::BASE_DEPENDENCY => [
			'mvc_router\mvc\Controller' 			=> [
				'name'         => self::CONTROLLER,
				'file'         => __DIR__.'/mvc/Controller.php',
				'is_singleton' => self::NONE,
			],
			'mvc_router\mvc\View'       			=> [
				'name'         => self::VIEW,
				'file'         => __DIR__.'/mvc/View.php',
				'is_singleton' => self::NONE,
			],
			'mvc_router\data\gesture\Manager' 		=> [
				'name'         => self::MANAGER,
				'file'         => __DIR__.'/data_gesture/Manager.php',
				'is_singleton' => self::NONE,
			],
			'mvc_router\data\gesture\Entity'  		=> [
				'name'         => self::ENTITY,
				'file'         => __DIR__.'/data_gesture/Entity.php',
				'is_singleton' => self::NONE,
			],
			'mvc_router\services\Service'           => [
				'name'         => self::SERVICE,
				'file'         => __DIR__.'/utils/services/Service.php',
				'is_singleton' => self::NONE,
			],
			'mvc_router\commands\Commands'        	=> [
				'name'         => self::COMMANDS,
				'file'         => __DIR__.'/utils/commands/Commands.php',
				'is_singleton' => self::SINGLETON,
			],
			'mvc_router\commands\Command'         	=> [
				'name'         => self::COMMAND,
				'file'         => __DIR__.'/utils/commands/Command.php',
				'is_singleton' => self::NONE,
			],
			'mvc_router\websockets\MessageComponent'=> [
				'name'         => self::WEBSOCKET,
				'file'         => __DIR__.'/utils/websockets/MessageComponent.php',
				'is_abstract'  => true
			],
		],
		self::CONTROLLER_DEPENDENCY => [
			'mvc_router\mvc\Routes'     			=> [
				'name'         => self::ROUTES_CONTROLLER,
				'file'         => __DIR__.'/mvc/controllers/Routes.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\mvc\Controller'
			],
		],
		self::VIEW_DEPENDENCY => [
			'mvc_router\mvc\views\Route' 			=> [
				'name'         => self::ROUTE_VIEW,
				'file'         => __DIR__.'/mvc/views/Route.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\mvc\View'
			],
		],
		self::DATA_MODELS_DEPENDENCY => [],
		self::SERVICE_DEPENDENCY => [
			'mvc_router\services\Json'              => [
				'name'         => self::JSON_SERVICE,
				'file'         => __DIR__.'/services/Json.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Yaml'              => [
				'name'         => self::YAML_SERVICE,
				'file'         => __DIR__.'/services/Yaml.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Error'             => [
				'name'         => self::ERROR_SERVICE,
				'file'         => __DIR__.'/services/Error.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Logger'            => [
				'name'         => self::LOGGER_SERVICE,
				'file'         => __DIR__.'/services/Logger.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Route'             => [
				'name'         => self::ROUTE_SERVICE,
				'file'         => __DIR__.'/services/Route.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service'
			],
			'mvc_router\services\FileGeneration'    => [
				'name'         => self::FILE_GENERATION_SERVICE,
				'file'         => __DIR__.'/services/FileGeneration.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Session'           => [
				'name'         => self::SESSION_SERVICE,
				'file'         => __DIR__.'/services/Session.php',
				'is_singleton' => self::SINGLETON,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Translate'         => [
				'name'         => self::TRANSLATION_SERVICE,
				'file'         => __DIR__.'/services/Translate.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\FileSystem'        => [
				'name'         => self::FILE_SYSTEM_SERVICE,
				'file'         => __DIR__.'/services/FileSystem.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Trigger'           => [
				'name'         => self::TRIGGER_SERVICE,
				'file'         => __DIR__.'/services/Trigger.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\TriggerRegisterer' => [
				'name'         => self::CLASS_TRIGGERS,
				'file'         => __DIR__.'/services/TriggerRegisterer.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Lock'              => [
				'name'         => self::LOCK_SERVICE,
				'file'         => __DIR__.'/services/Lock.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\UrlGenerator'      => [
				'name'         => self::URL_GENERATOR_SERVICE,
				'file'         => __DIR__.'/services/UrlGenerator.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
			'mvc_router\services\Websocket'      	=> [
				'name'         => self::WEBSOCKET_SERVICE,
				'file'         => __DIR__.'/services/Websocket.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\services\Service',
			],
		],
		self::HELPER_DEPENDENCY => [
			'mvc_router\parser\PHPDocParser' 		=> [
				'name'         => self::PHP_DOC_PARSER,
				'file'         => __DIR__.'/utils/parsers/PHPDocParser.php',
				'is_singleton' => self::SINGLETON,
			],
			'mvc_router\parser\YamlParser' 		=> [
				'name'         => self::YAML_PARSER,
				'file'         => __DIR__.'/utils/parsers/YamlParser.php',
				'is_singleton' => self::NONE,
			],
			'mvc_router\queues\Queue'        		=> [
				'name'         => self::QUEUE,
				'file'         => __DIR__.'/utils/queues/Queue.php',
				'is_singleton' => false
			],
			'mvc_router\queues\QueueList'    		=> [
				'name'         => self::QUEUE_LIST,
				'file'         => __DIR__.'/utils/queues/QueueList.php',
				'is_singleton' => self::SINGLETON,
			],
			
			'mvc_router\router\Router' 				=> [
				'name'         => self::ROUTER,
				'file'         => __DIR__.'/mvc/Router.php',
				'is_singleton' => self::SINGLETON,
			],
			
			'mvc_router\helpers\Helpers' 			=> [
				'name'         => self::HELPERS,
				'file'         => __DIR__.'/utils/Helpers.php',
				'is_singleton' => self::SINGLETON,
			],
		],
		self::ROUTER_DEPENDENCY => [
			'mvc_router\router\Router' 				=> [
				'name'         => self::ROUTER,
				'file'         => __DIR__.'/mvc/Router.php',
				'is_singleton' => self::SINGLETON,
			],
		],
		self::COMMAND_DEPENDENCY => [
			'mvc_router\commands\HelpCommand'  		=> [
				'name'         => self::HELP_COMMAND,
				'file'         => __DIR__.'/commands/HelpCommand.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\commands\Command',
			],
			'mvc_router\commands\TestCommand'     	=> [
				'name'         => self::TEST_COMMAND,
				'file'         => __DIR__.'/commands/TestCommand.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\commands\Command',
			],
			'mvc_router\commands\GenerateCommand' 	=> [
				'name'         => self::GENERATE_COMMAND,
				'file'         => __DIR__.'/commands/GenerateCommand.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\commands\Command',
			],
			'mvc_router\commands\CloneCommand'    	=> [
				'name'         => self::CLONE_COMMAND,
				'file'         => __DIR__.'/commands/CloneCommand.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\commands\Command',
			],
			'mvc_router\commands\InstallCommand'  	=> [
				'name'         => self::INSTALL_COMMAND,
				'file'         => __DIR__.'/commands/InstallCommand.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\commands\Command',
			],
			'mvc_router\commands\StartCommand'  	=> [
				'name'         => self::START_COMMAND,
				'file'         => __DIR__.'/commands/StartCommand.php',
				'is_singleton' => self::NONE,
				'parent'       => 'mvc_router\commands\Command',
			],
		],
		self::COMPOSER_DEPENDENCY => [
			'Curl\Curl' 							=> [
				'name'         => self::CLASS_REQUEST,
				'file'         => __DIR__.'/../vendor/autoload.php',
				'is_singleton' => self::NONE,
			],
			'Ratchet\App' 							=> [
				'name'         => self::RATCHET_APP_WS,
				'file'         => __DIR__.'/../vendor/autoload.php',
				'is_singleton' => self::NONE,
				'params' 	   => [
					'host' => [
						'type' => 'string',
						'default' => 'localhost'
					],
					'port' => [
						'type' => 'int',
						'default' => 8080
					],
					'address' => [
						'type' => 'string',
						'default' => '127.0.0.1'
					],
					'loop' => [
						'default' => null
					]
				]
			],
			'Ratchet\Server\EchoServer'				=> [
				'name' => self::WEBSOCKET_DEFAULT_CONTROLLER,
				'file' => __DIR__.'/../vendor/autoload.php',
				'is_singleton' => self::NONE,
			],
			'\Symfony\Component\Yaml\Yaml'          => [
				'name' => 'yaml_parser',
				'file' => __DIR__.'/../vendor/autoload.php',
				'is_singleton' => self::NONE,
			],
		],
		self::WEBSOCKET_DEPENDENCY => []
	];
	
	/**
	 * @param null|integer $type
	 * @param bool $flat
	 * @return array
	 */
	public static function get_dependencies($type = null, $flat = false) {
		if(is_null($type) && !$flat) {
			return self::$dependencies;
		}
		if(is_null($type) && $flat) {
			$deps = array_values(self::$dependencies);
			return array_merge(...$deps);
		}
		return self::$dependencies[ $type];
	}
	
	/**
	 * @return void
	 */
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
		return isset(self::get_dependencies(null, true)[$class]);
	}

	/**
	 * @param       $classname
	 * @param array $arguments
	 * @return Base
	 * @throws Exception
	 */
	public static function get_from_classname($classname, ...$arguments) {
		if(gettype($classname) === 'object' && get_class($classname) === 'ReflectionClass') {
			/** @var ReflectionClass $classname */
			$classname = $classname->getName();
		}
		if(substr($classname, 0, 1) === '\\') {
			$classname = substr($classname, 1, strlen($classname) - 1);
		}
		$deps = self::get_dependencies(null, true);
		if(isset($deps[$classname])) {
			if(!class_exists($classname)) {
				if(isset($deps[$classname]['parent'])) {
					$parent = $deps[$classname]['parent'];
					if(substr($parent, 0, 1) === '\\') {
						$parent = substr($parent, 1, strlen($parent) - 1);
					}
					if(isset($deps[$parent]) && strstr($deps[$parent]['file'], '.')) {
						require_once realpath($deps[$parent]['file']);
					}
				}
				if(realpath($deps[$classname]['file']) && strstr($deps[$classname]['file'], '.')) {
					require_once realpath( $deps[ $classname ][ 'file' ] );
				}
			}
			if(isset($deps[$classname]['is_abstract']) && $deps[$classname]['is_abstract']) {
				return null;
			}
			$instanciate_method = isset($deps[$classname]['method']) ? $deps[$classname]['method'] : 'create';
			return (isset($deps[$classname]['is_singleton']) && $deps[$classname]['is_singleton']) ||
				   (isset($deps[$classname]['is_factory']) && $deps[$classname]['is_factory'])
				? $classname::$instanciate_method(...$arguments) : new $classname(...$arguments);
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
		$deps = self::get_dependencies(null, true);
		if(isset($deps[$classname])) {
			return $deps[$classname]['name'];
		}
		return null;
	}

	/**
	 * @param string $name
	 * @return string|null
	 */
	public static function get_class_from_name($name) {
		$deps = self::get_dependencies(null, true);
		foreach ($deps as $dependency_class => $dependency) {
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
	
	/**
	 * @return void
	 */
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
		$deps = self::get_dependencies(null, true);
		foreach ($deps as $dependency_class => $dependency_details) {
			if(substr($dependency_class, 0, 1) !== '\\') {
				$dependency_class = '\\'.$dependency_class;
			}
			$params = [];
			if(!empty($dependency_details['params'])) {
				foreach ($dependency_details['params'] as $param_name => $param) {
					$params[] = (isset($param['type']) ? $param['type'].' ' : '').'$'.$param_name.(isset($param['default']) || is_null($param['default']) ? ' = '.(is_null($param['default']) ? 'null' : (is_string($param['default']) ? '"'.$param['default'].'"' : $param['default'])) : '');
				}
			}
			$final_class .= "\t * @method ".$dependency_class." get_".$dependency_details['name']."(".implode(', ', $params).")\n";
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
	
	/**
	 * @return void
	 */
	private static function delete_dependency_wrapper() {
		unlink(__DIR__.'/DependencyWrapper.php');
	}
	
	/**
	 * @return void
	 */
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
	 * @param int $dependency_type
	 * @param mixed $type
	 */
	public static function add_custom_dependency($class, $name, $file, $parent = null, $dependency_type = self::BASE_DEPENDENCY, $type = self::NONE) {
		if(substr($class, 0, 1) === '\\') {
			$class = substr($class, 1, strlen($class) - 1);
		}
		if(is_null($type)) {
			Dependency::$dependencies[ $dependency_type][ $class] = [
				'name' => $name,
				'file' => $file,
				'is_singleton' => self::NONE,
			];
		}
		else {
			Dependency::get_dependencies($dependency_type)[$class] = [
				'name' => $name,
				'file' => $file,
				($type === self::SINGLETON ? 'is_singleton' : 'is_factory') => true,
			];
		}
		if(!is_null($parent)) {
			Dependency::get_dependencies($dependency_type)[$class]['parent'] = $parent;
		}
		if(!is_file(__DIR__.'/DependencyWrapper.php')
		   || (is_file(__DIR__.'/DependencyWrapper.php')
			   && !strstr('get_'.$name, file_get_contents(__DIR__.'/DependencyWrapper.php')))) {
			self::delete_dependency_wrapper();
			self::require_dependency_wrapper();
		}
	}
	
	/**
	 * @param array[] ...$dependencies
	 */
	public static function add_custom_dependencies(...$dependencies) {
		foreach ($dependencies as $dependency) {
			self::add_custom_dependency(
				$dependency['class'],
				$dependency['name'],
				$dependency['file'],
				(isset($dependency['parent']) ? $dependency['parent'] : null),
				(isset($dependency['dependency_type']) ? $dependency['dependency_type'] : null),
				(isset($dependency['type']) ? $dependency['type'] : self::NONE)
			);
		}
	}
	
	/**
	 * @param $old_class
	 * @param $new_class
	 * @param $details
	 */
	public static function extend_dependency($old_class, $new_class, $details) {
		$type = isset($details['type']) ? $details['type'] : self::NONE;
		$dep_type = isset($details['dependency_type']) ? $details['dependency_type'] : self::BASE_DEPENDENCY;
		self::$dependencies[ $dep_type][ $old_class][ 'name'] = '_'.self::$dependencies[ $dep_type][ $old_class][ 'name'];
		$parent = isset($details['parent']) ? $details['parent'] : $old_class;
		self::add_custom_dependency($new_class, $details['name'], $details['file'], $parent, $dep_type, $type);
	}
	
	/**
	 * @param array[] ...$dependencies
	 */
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
	 * @param $class
	 * @param $name
	 * @param $file
	 * @param null|integer $type
	 */
	public static function add_custom_view($class, $name, $file, $type = self::NONE) {
		self::add_custom_dependency($class, $name, $file, '\mvc_router\mvc\View', self::VIEW_DEPENDENCY, $type);
	}
	
	/**
	 * @param array[] ...$views
	 */
	public static function add_custom_views(...$views) {
		foreach ($views as $view_details) {
			$type = isset($view_details['type']) ? $view_details['type'] : self::NONE;
			self::add_custom_view($view_details['class'], $view_details['name'], $view_details['file'], $type);
		}
		self::require_dependency_wrapper();
	}
	
	/**
	 * @param $class
	 * @param $name
	 * @param $file
	 * @param string $class_type = Manager|Entity
	 * @param null $type
	 * @throws Exception
	 */
	public static function add_custom_data_model($class, $name, $file, $class_type, $type = self::NONE) {
		switch($class_type) {
			case self::MANAGER:
				self::add_custom_dependency($class, $name, $file, '\mvc_router\data\gesture\Manager',
				                            self::DATA_MODELS_DEPENDENCY, $type);
				break;
			case self::ENTITY:
				self::add_custom_dependency($class, $name, $file, '\mvc_router\data\gesture\Entity',
				                            self::DATA_MODELS_DEPENDENCY, $type);
				break;
			default:
				throw new Exception('Auncun autre type de classe que `Entity` ou `Manager` ne sont acceptés !');
		}
	}
	
	/**
	 * @param array ...$data_models
	 * @throws Exception
	 */
	public static function add_custom_data_models(...$data_models) {
		foreach ($data_models as $dm_details) {
			$type = isset($dm_details['type']) ? $dm_details['type'] : self::NONE;
			self::add_custom_data_model($dm_details['class']['name'], $dm_details['name'], $dm_details['file'], $dm_details['class']['type'], $type);
		}
		self::require_dependency_wrapper();
	}

	/**
	 * @param string 		$class
	 * @param string 		$name
	 * @param string 		$file
	 * @param string|null	$type
	 */
	public static function add_custom_controller($class, $name, $file, $type = self::NONE) {
		self::add_custom_dependency($class, $name, $file, '\mvc_router\mvc\Controller',
		                            self::CONTROLLER_DEPENDENCY, $type);
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
		self::add_custom_dependency($class, $name, $file, '\mvc_router\commands\Command',
		                            self::COMMAND_DEPENDENCY, $type);
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
	 * @param $class
	 * @param $name
	 * @param $file
	 * @param null $type
	 */
	public static function add_custom_ws_controller($class, $name, $file, $type = self::NONE) {
		self::add_custom_dependency($class, $name, $file, '\mvc_router\websockets\MessageComponent',
		                            self::WEBSOCKET_DEPENDENCY, $type);
	}
	
	/**
	 * @param array[] ...$ws_controllers
	 */
	public static function add_custom_ws_controllers(...$ws_controllers) {
		foreach ($ws_controllers as $cws_ctrl_details) {
			$type = isset($cws_ctrl_details['type']) ? $cws_ctrl_details['type'] : self::NONE;
			self::add_custom_ws_controller($cws_ctrl_details['class'], $cws_ctrl_details['name'], $cws_ctrl_details['file'], $type);
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
			if(self::method_exists('get_'.$name)) {;
				return self::get_from_classname(self::get_class_from_method('get_'.$name), ...$arguments);
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
		foreach (self::get_dependencies(self::CONTROLLER_DEPENDENCY) as $class => $dependency) {
			$controllers[$class] = $dependency;
		}
		return $controllers;
	}

	/**
	 * @return array
	 */
	public static function commands() {
		$commands = [];
		foreach (self::get_dependencies(self::COMMAND_DEPENDENCY) as $class => $dependency) {
			$commands[$class] = $dependency;
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
				preg_match('/\([^\)]+\)/', $_line, $matches);
				if(!empty($matches)) {
					str_replace($matches[0], '', $_line);
					$_line = explode('(', $_line)[0];
				}
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
		$deps = self::get_dependencies(null, true);
		foreach ($deps as $dependency) {
			if($dependency['name'] === $elem) return true;
		}
		return false;
	}

	/**
	 * @param $elem
	 * @return bool
	 */
	public static function is_controller($elem) {
		foreach (self::get_dependencies(self::CONTROLLER_DEPENDENCY) as $class => $dependency) {
			if($dependency['name'] === $elem) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $elem
	 * @return bool
	 */
	public static function is_command($elem) {
		foreach (self::get_dependencies(self::COMMAND_DEPENDENCY) as $class => $dependency) {
			if($dependency['name'] === $elem) {
				return true;
			}
		}
		return false;
	}

	public static function is_view($elem) {
		foreach (self::get_dependencies(self::VIEW_DEPENDENCY) as $class => $dependency) {
			if($dependency['name'] === $elem) {
				return true;
			}
		}
		return false;
	}

	public static function is_composer($elem) {
		foreach (self::get_dependencies(self::COMPOSER_DEPENDENCY) as $class => $dependency) {
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
				preg_match('/\([^\)]+\)/', $_line, $matches);
				if(!empty($matches)) {
					str_replace($matches[0], '', $_line);
					$_line = explode('(', $_line)[0];
				}
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
		if (Conf::is_in($class)) {
			Conf::get_from_classname($class);
		}
		elseif(Dependency::is_in($class)) {
			Dependency::get_from_classname($class);
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
		foreach (self::get_dependencies(self::DATA_MODELS_DEPENDENCY) as $dependency) {
			if(isset($dependency['parent']) && ($dependency['parent'] === 'mvc_router\data\gesture\Manager' || $dependency['parent'] === '\mvc_router\data\gesture\Manager')) {
				$managers_class[str_replace([__SITE_NAME__.'_', '_manager'], '', $dependency['name'])] = self::get_wrapper_factory()->get_dependency_wrapper()->{"get_{$dependency['name']}"}();
			}
		}
		return $managers_class;
	}
}
