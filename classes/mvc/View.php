<?php


namespace mvc_router\mvc;


use Exception;
use mvc_router\Base;

class View extends Base {
	const UTF8 = 'utf-8';
	protected $encoding = self::UTF8;

	const HTML = 'html';
	const JSON = 'json';

	private $use_materialize 	= false;
	private $use_bootstrap 		= false;
	private $use_none 			= true;

	private $vars = [];

	protected $content_types = [
		'json' => 'application/json',
		'html' => 'text/html',
		'text' => 'plain/text',
	];

	/** @var \mvc_router\services\Translate $translate */
	public $translate;

	// translation helper
	protected function __($text, $params = []) {
		return $this->translate->__($text, $params);
	}

	// for use css frameworks
	final protected function bootstrapV4Top($use_jquery = true) {
		$this->use_bootstrap = true;
		$this->use_materialize = false;
		$this->use_none = false;
		return '
	<meta charset="'.$this->encoding.'"
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link   href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" 
			rel="stylesheet" 
			integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" 
			crossorigin="anonymous">
	'.($use_jquery ? '<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>' : '').'
    <script src="https://getbootstrap.com/docs/4.0/assets/js/vendor/popper.min.js" 
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" 
            crossorigin="anonymous"></script>
    <script src="https://getbootstrap.com/docs/4.0/dist/js/bootstrap.min.js" 
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" 
            crossorigin="anonymous"></script>
';
	}
	final protected function materializeCssV1Top($use_jquery = true) {
		$this->use_bootstrap = false;
		$this->use_materialize = true;
		$this->use_none = false;
		return '
	<meta charset="'.$this->encoding.'" />
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	
	<link rel="stylesheet" href="https://materializecss.com/bin/materialize.css" />

	'.($use_jquery ? '<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>' : '').'
    <script src="https://materializecss.com/bin/materialize.js"></script>
';
	}

	final public function is_use_materialize() {
		return $this->use_materialize;
	}

	final public function is_use_bootstrap() {
		return $this->use_bootstrap;
	}

	final public function is_use_none() {
		return $this->use_none;
	}

	final public function assign($var, $value) {
		$this->vars[$var] = $value;
	}

	final public function get($var) {
		return isset($this->vars[$var]) ? $this->vars[$var] : null;
	}

	public function header($content_type = self::HTML) {
		header('Content-Type: '.$this->content_types[$content_type]);
	}

	public function render(): string {
		return '';
	}
	public function __toString() {
		try {
			return $this->render();
		}
		catch (Exception $e) {
			return $e->getMessage();
		}
	}
}