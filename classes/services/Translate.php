<?php


namespace mvc_router\services;


class Translate extends Service {
	const FR = 'fr-FR';
	const EN = 'en-GB';
	const US = 'en-US';

	protected $languages = [
		self::FR => self::FR,
		self::EN => self::EN,
		self::US => self::US,
	];

	protected function get_current_language() {
		return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : geoip_country_code_by_name(gethostname());
	}

	public function __($text, $params = []) {
		return $this->get_translated($text) !== false ? '[ Missing Translation ]'.$text : $this->get_translated($text);
	}

	protected function get_translated($key, $lang = self::FR) {
		return isset($this->get_array($lang)[$key]) ? $this->get_array($lang)[$key] : false;
	}

	protected function get_array($lang = self::FR) {
		if(!is_dir(__DIR__.'/../translations')) {
			mkdir(__DIR__.'/../translations', 0777, true);
		}
		$file_path = __DIR__.'/../translations/translation_'.$lang.'.json';
		if(!is_file($file_path)) {
			file_put_contents($file_path, $this->inject->get_service_json()->encode([]));
		}
		return $this->inject->get_service_json()->decode(file_get_contents($file_path));
	}
}