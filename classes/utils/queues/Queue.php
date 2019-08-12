<?php


namespace mvc_router\queues;


use mvc_router\Base;
use mvc_router\services\Logger;

class Queue extends Base {
	protected $name;

	protected $base_path = __DIR__.'/../../queues';
	protected $extension = '.json';

	protected $callback;

	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	public function setCallback($callback) {
		$this->callback = $callback;
		return $this;
	}

	private function get_path() {
		return realpath($this->base_path).'/'.$this->name.$this->extension;
	}

	public function create() {
		if($this->name) {
			if(!is_file($this->get_path())) {
				file_put_contents($this->get_path(), $this->inject->get_service_json()->encode(new \stdClass()));
			}
		}
		return $this;
	}

	public function push($element): bool {
		$lock = $this->inject->get_service_lock();
		if(!$lock->is_locked($this->get_path())) {
			$lock->lock($this->get_path());
			$json = $this->inject->get_service_json();
			$old_content   = $json->decode(file_get_contents($this->get_path()), true);
			$old_content[] = [$element];
			file_put_contents($this->get_path(), $json->encode($old_content));
			$lock->unlock($this->get_path());
			return true;
		}
		return false;
	}

	public function pop(callable $callback = null): void {
		$lock = $this->inject->get_service_lock();
		if(!$lock->is_locked($this->get_path())) {
			$json = $this->inject->get_service_json();
			$lock->unlock($this->get_path());
			$content = $json->decode(file_get_contents($this->get_path()), true);
			$params = array_shift($content);
			file_put_contents($this->get_path(), $json->encode($content));
			$lock->lock($this->get_path());
			$default_callback = $this->callback;
			is_null($callback) ? $default_callback(...$params) : $callback(...$params);
		}
	}

	public function clear(): bool {
		$json = $this->inject->get_service_json();
		return file_put_contents($this->get_path(), $json->encode(new \stdClass()));
	}

	public function remove(): bool {
		return unlink($this->get_path());
	}

	public function isEmpty(): bool {
		$json = $this->inject->get_service_json();
		$content = $json->decode(file_get_contents($this->get_path()), true);
		return empty($content);
	}

	public function copy($name): Queue {
		$cloned = clone $this;
		$cloned->setName($name)->create();
		return $cloned;
	}

	public function toArray(): array {
		return get_object_vars($this);
	}

	public function start() {
		$logger = $this->inject->get_service_logger()->types(Logger::CONSOLE);
		$already_write_waiting = false;
		do {
			if($this->isEmpty()) {
				if(!$already_write_waiting) {
					$logger->log(ucfirst($this->name).' queue waiting new message ...');
					$already_write_waiting = true;
				}
			}
			else {
				if (is_array($this->callback)) {
					$callback = implode('::', $this->callback);
				} else {
					$callback = $this->callback;
				}
				$this->pop($callback);
				$already_write_waiting = false;
			}
		} while (true);
	}
}