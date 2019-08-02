<?php


namespace mvc_router\services;


class FileSystem extends Service {
	public function browse_root(callable $callback, $recursive = false) {
		$this->browse_dir($callback, $recursive, __DIR__.'/../..');
	}

	public function browse_dir(callable $callback, $recursive = false, $path = __DIR__.'/../..') {
		$directory = realpath($path);
		$dir = opendir($directory);
		while (($elem = readdir($dir)) !== false) {
			if($elem !== '.' && $elem !== '..' && substr($elem, 0, 1) !== '.' && $elem !== 'vendor') {
				if($recursive) {
					if (is_dir($path.'/'.$elem)) {
						$this->browse_dir($callback, $recursive, $path.'/'.$elem);
					} elseif (is_file($path.'/'.$elem)) {
						$callback($path.'/'.$elem);
					}
				}
				else {
					if(is_file($path.'/'.$elem)) {
						$callback($path.'/'.$elem);
					}
				}
			}
		}
	}
}