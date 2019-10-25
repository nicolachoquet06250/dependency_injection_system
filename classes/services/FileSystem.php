<?php


namespace mvc_router\services;


use Exception;

/**
 * Comptient des fonctions pour pouvoir parcourir le système de fichier de votre système d'exploitation
 *
 * @package mvc_router\services
 */
class FileSystem extends Service {

	const TEXT        = 0;
	const LOG         = 1;
	const HTML        = 2;
	const JSON        = 3;
	const XML         = 4;
	const SVG         = 5;
	const CONTROLLER  = 6;
	const VIEW        = 7;
	const SERVICE     = 8;
	const INTERFACE   = 9;
	const CONF        = 10;
	const MODEL       = 11;
	const QUEUE_CLASS = 12;
	const COMMAND	  = 13;

	const EXTENSIONS = [
		self::TEXT        => '.txt',
		self::LOG         => '.log',
		self::HTML        => '.html',
		self::JSON        => '.json',
		self::XML         => '.xml',
		self::SVG         => '.svg',
		self::CONTROLLER  => '.php',
		self::VIEW        => '.php',
		self::SERVICE     => '.php',
		self::CONF        => '.php',
		self::MODEL       => '.php',
		self::QUEUE_CLASS => '.php',
		self::COMMAND	  => '.php',
	];

	const DEFAULT_NAMESPACES = [
		self::CONTROLLER => 'mvc_router\mvc\controllers',
		self::VIEW => 'mvc_router\mvc\views',
		self::SERVICE => 'mvc_router\services',
		self::INTERFACE => 'mvc_router\interfaces',
		self::CONF => 'mvc_router\confs',
		self::MODEL => 'mvc_router\mvc\models',
		self::QUEUE_CLASS => 'mvc_router\queues',
		self::COMMAND => 'mvc_router\commands',
	];


	/**
	 * @param callable $callback
	 * @param bool     $recursive
	 */
	public function browse_root(callable $callback, bool $recursive = false) {
		$this->browse_dir($callback, $recursive, __DIR__.'/../..');
	}

	/**
	 * @param callable $callback
	 * @param bool     $recursive
	 * @param string   $path
	 */
	public function browse_dir(callable $callback, bool $recursive = false, string $path = __DIR__.'/../..') {
		$slash = $this->inject->get_helpers()->get_slash();
		if($slash !== '/') {
			$path = str_replace( '/', '\\', $path );
		}
		$directory = realpath($path);
		$dir = opendir($directory);
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..' && substr($elem, 0, 1) !== '.' && $elem !== 'vendor') {
				if($recursive) {
					if (is_dir($path.$slash.$elem)) {
						$this->browse_dir($callback, $recursive, $path.$slash.$elem);
					} elseif (is_file($path.$slash.$elem)) {
						$callback($path.$slash.$elem);
					}
				}
				else {
					if(is_file($path.$slash.$elem)) {
						$callback($path.$slash.$elem);
					}
				}
			}
		}
	}

	public function list_directories($path = __DIR__.'/../../', $complete_path = true) {
		$slash = $this->inject->get_helpers()->get_slash();
		$directory = realpath($path);
		$dir = opendir($directory);
		$dirs = [];
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..' && substr($elem, 0, 1) !== '.' && $elem !== 'vendor') {
				if(is_dir($path.$slash.$elem)) {
					$dirs[] = ($complete_path ? $path.$slash : '').$elem;
				}
			}
		}
		return $dirs;
	}

	/**
	 * @param string $path
	 * @param string $dir_name
	 * @return bool
	 * @throws Exception
	 */
	public function create_dir(string $path, string $dir_name) {
		$slash = $this->inject->get_helpers()->get_slash();
		$path_end = substr($path, strlen($path) - 1, 1) === $slash ? '' : $slash;
		if(realpath($path)) {
			return mkdir($path.$path_end.$dir_name, 0777, true);
		}
		throw new Exception('Le chemin du répertoir n\'existe pas !');
	}

	/**
	 * @param string      $path
	 * @param string      $file_name
	 * @param int         $type
	 * @param string|null $namespace
	 * @param string      $default_content
	 * @return bool|int
	 * @throws Exception
	 */
	public function create_file(string $path, string $file_name, int $type = self::TEXT, string $namespace = null, $default_content = '') {
		$slash = $this->inject->get_helpers()->get_slash();
		if(in_array($type, array_keys(self::EXTENSIONS))) {
			switch ($type) {
				case self::TEXT:
				case self::LOG:
				case self::HTML:
					$content = '';
					break;
				case self::JSON:
					$content = $this->inject->get_service_json()->encode($default_content ? $default_content : []);
					break;
				case self::XML:
					$content = '<?xml version="1.0" encoding="UTF-8"?>
<root>
	
</root>';
					break;
				case self::SVG:
					$content = '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="500" height="500" version="1.1" xmlns="http://www.w3.org/2000/svg">
	
</svg>';
					break;
				case self::CONTROLLER:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php
	namespace '.$namespace.';
	
	use mvc_router\mvc\Controller;
	
	class '.ucfirst($file_name).' extends Controller {
		/**
		 * @route /'.strtolower($file_name).'
		 *
		 * @return string
		 */
		public function index() {
			return \'\';
		}
	}
';
					break;
				case self::VIEW:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php
	namespace '.$namespace.';
	
	use mvc_router\mvc\View;
	
	class '.ucfirst($file_name).' extends View {
		public function render(): string {
			$this->header();
			
			return \'\';
		}
	}
';
					break;
				case self::SERVICE:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php
	namespace '.$namespace.';
	'.($namespace === self::DEFAULT_NAMESPACES[$type] ? '' : "\n".'use mvc_router\services\Service;'."\n").'
	class '.ucfirst($file_name).' extends Service {}
';
					break;
				case self::INTERFACE:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php

	namespace '.$namespace.';
	
	interface '.ucfirst($file_name).' {}					
';
					break;
				case self::CONF:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php
	
	namespace '.$namespace.';
	
	use mvc_router\Base;
	
	class '.ucfirst($file_name).' extends Base {}
';
					break;
				case self::MODEL:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php
	namespace '.$namespace.';
	
	use mvc_router\mvc\Model;
	
	class '.ucfirst($file_name).' extends Model {}
';
					break;
				case self::QUEUE_CLASS:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php
	namespace '.$namespace.';
	
	class '.ucfirst($file_name).' {}
';
					break;
				case self::COMMAND:
					if(!$namespace) {
						$namespace = self::DEFAULT_NAMESPACES[$type];
					}
					$content = '<?php
	
	namespace '.$namespace.';
	'.($namespace === self::DEFAULT_NAMESPACES[$type] ? '' : "\n".'use mvc_router\commands\Command;'."\n").'
	class '.ucfirst($file_name).' extends Command {}	
';
					break;
				default:
					throw new Exception('Le type de fichier choisis n\'est pas disponible');
					break;
			}
			if(!realpath($path)) {
				$path = explode('/', $path);
				$dir_name = array_pop($path);
				$path = implode($path);
				if(!$this->create_dir($path, $dir_name)) {
					throw new Exception('Le repertoir n\'a pas pu être créé !');
				}
			}
			$dir_name = $dir_name ?? '';
			return file_put_contents($path.$slash.$dir_name.$file_name.self::EXTENSIONS[$type], $content);
		} else throw new Exception('Le type de fichier choisis n\'est pas disponible');
	}

	public function read_file(string $path) {
		return file_get_contents($path);
	}
}