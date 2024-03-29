<?php


namespace mvc_router\services;


/**
 * @package mvc_router\services
 */
class Logger extends Service {
	const CONSOLE = 1;
	const FILE = 2;

	protected $types = [];
	protected $path = __DIR__.'/../../';
	protected $file;
	protected $separator = '';

	public function after_construct() {
		parent::after_construct();
		$this->file = date('Y-m-d').'.log';
	}

	public function get_path_file() {
		if(!is_dir($this->path)) {
			mkdir($this->path, 0777, true);
		}
		return $this->path.$this->file;
	}

	public function get_file_content() {
		return file_get_contents($this->get_path_file());
	}

	public function remove_file() {
		unlink($this->get_path_file());
	}

	public function types(...$types) {
		$this->types = array_merge($this->types, $types);
		return $this;
	}

	public function file($path, $file) {
		$this->path = $path;
		$this->file = $file;
		return $this;
	}

	public function separator($separator) {
		$this->separator = $separator;
		return $this;
	}

	public function log_separator() {
		return $this->log($this->separator);
	}

	protected function format_header_log($with_user_name = false) {
		return ($with_user_name ? $this->get_username().'@'.$this->get_hostname().'~' : '').date('Y-m-d_H:i:s').' | ';
	}

	protected function log_file($message) {
		$complete_path = $this->get_path_file();
		if(!is_file($complete_path)) {
			touch($complete_path);
		}
		$content = $this->get_file_content();
		$content .= $message."\n";
		file_put_contents($complete_path, $content);
	}

	protected function log_console($message) {
		echo $message."\n";
	}

	public function log($message) {
		if(is_array($message)) {
			foreach ($message as $item => $value) {
				if(is_int($item)) {
					$this->log($value);
				} else {
					$this->log("{$item} => {$value}");
				}
				return null;
			}
		}
		$message = $this->format_header_log(true).$message;
		foreach ($this->types as $type) {
			switch ($type) {
				case self::CONSOLE:
					$this->log_console($message);
					break;
				case self::FILE:
					$this->log_file($message);
			}
		}
		return $this;
	}

	public function log_if($message, bool $condition) {
		if($condition) {
			$this->log($message);
		}
		return $this;
	}
}