<?php


namespace mvc_router\mvc;


use mvc_router\Base;

class Controller extends Base {
	const JSON = 0;
	const XML = 1;
	const HTML = 2;
	const TEXT = 3;

	private $content_types = [
		self::JSON => 'application/json',
		self::XML => 'application/xml',
		self::HTML => 'text/html',
		self::TEXT => 'plain/text',
	];

	private $parameters = [];

	/**
	 * @route_disabled
	 * @param mixed $value
	 * @return Controller
	 */
	public final function add_parameter($value) {
		$this->parameters[] = $value;
		return $this;
	}

	/**
	 * @route_disabled
	 * @param array $parameters
	 * @return Controller
	 */
	public final function add_parameters(array $parameters) {
		$this->parameters = $parameters;
		return $this;
	}

	/**
	 * @return array
	 */
	protected final function params() {
		return $this->parameters;
	}

	/**
	 * @param $key
	 * @return mixed|null
	 */
	protected final function param($key) {
		return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
	}

	private function define_content_type($type) {
		header('Content-Type: '.$this->content_types[$type].';charset=utf-8');
	}

	private function render_not_available($type) {
		$this->inject->get_service_error()->error400(strtoupper($type).' content type is not available for the moment</b>');
	}

	protected function json($message) {
		$this->define_content_type(self::JSON);
		return $this->inject->get_service_json()->encode($message);
	}

	protected function text($message) {
		$this->define_content_type(self::TEXT);
		return $message;
	}

	protected function html($message) {
		$this->define_content_type(self::HTML);
		return $message;
	}

	protected function render($message, $type = self::TEXT) {
		$method = 'text';
		switch ($type) {
			case self::JSON:
				$method = 'json';
				break;
			case self::XML:
				$this->render_not_available('xml');
				break;
			case self::HTML:
				$method = 'html';
				break;
			case self::TEXT:
				$method = 'text';
				break;
			default:
				$method = 'text';
				break;
		}
		return $this->$method($message);
	}
}
