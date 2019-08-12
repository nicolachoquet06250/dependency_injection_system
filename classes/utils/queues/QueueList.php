<?php


namespace mvc_router\queues;


use mvc_router\Base;
use mvc_router\interfaces\Singleton;

class QueueList extends Base implements Singleton {
	private static $instance;
	private static $queues = [];

	public static function create() {
		if(is_null(self::$instance)) {
			self::$instance = new QueueList();
		}
		return self::$instance;
	}

	public function after_construct() {
		parent::after_construct();
		if(!realpath(__DIR__.'/../../queues')) {
			mkdir(__DIR__.'/../../queues', 0777, true);
		}
		$fs = $this->inject->get_service_fs();
		$queues = [];
		$fs->browse_dir(function ($path) use (&$queues) {
			$file = explode('/', $path)[count(explode('/', $path)) - 1];
			$name = explode('.', $file)[0];
			$queues[$name] = $this->inject->get_util_queue()->setName($name)->create();
		}, false, realpath(__DIR__.'/../../queues'));
		self::$queues = $queues;
	}

	public function get_all() {
		return self::$queues;
	}

	public function get($name): Queue {
		return isset(self::$queues[$name]) ? self::$queues[$name] : $this->add($name);
	}

	public function add($name): Queue {
		self::$queues[$name] = $this->inject->get_util_queue()->setName($name)->create();
		return self::$queues[$name];
	}

	public function remove($name) {
		if(isset(self::$queues[$name])) {
			/** @var Queue $queue */
			$queue = self::$queues[$name];
			$queue->remove();
			unset(self::$queues[$name]);
		}
	}
}