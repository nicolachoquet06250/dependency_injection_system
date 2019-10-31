<?php


namespace mvc_router\http\errors;


use Exception;
use Throwable;

class Exception401 extends Exception {
	protected $return_type;
	
	public function __construct($message = "", $return_type = 0, $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
		$this->return_type = $return_type;
	}

	public function getReturnType() {
		return $this->return_type;
	}
}