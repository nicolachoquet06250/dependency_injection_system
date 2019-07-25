<?php


namespace mvc_router\services;


class Logger extends Service {
	const CONSOLE = 1;
	const FILE = 2;

	protected $types = [];
	protected $path = __DIR__.'/../../';
	protected $file;

	public function __construct() {
		parent::__construct();
		$this->file = date('Y-m-d').'.log';
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

	protected function format_header_log($with_user_name = false) {
		return ($with_user_name ? get_current_user().'@'.gethostname().'~' : '').date('Y-m-d_H:i:s').' | ';
	}

	protected function log_file($message) {
		$complete_path = $this->path.$this->file;
		if(!is_file($complete_path)) {
			touch($complete_path);
		}
		$content = file_get_contents($complete_path);
		$content .= $message."\n";
		file_put_contents($complete_path, $content);
	}

	protected function log_console($message) {
		echo $message."\n";
	}

	public function log($message) {
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
	}
}