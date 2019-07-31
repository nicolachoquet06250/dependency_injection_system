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

	protected $languages_names = [
		self::FR => 'Français',
		self::EN => 'Anglais - RU',
		self::US => 'Anglais - US'
	];

	protected static $default_language = self::FR;

	protected $file_tpl = '%__DIR__%/../translations/translation_%lang%.json';

	protected function encode_text($text) {
		return base64_encode($text);
	}

	public function decode_text($text) {
		return base64_decode($text);
	}

	protected function get_current_language() {
		return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? explode(',', explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0])[1] : geoip_country_code_by_name(gethostname());
	}

	public function set_default_language($lang = self::FR) {
		self::$default_language = $lang;
	}

	public function get_default_language() {
		return self::$default_language;
	}

	public function get_languages() {
		return $this->languages_names;
	}

	protected function get_file_path($lang) {
		$file_tpl = $this->file_tpl;
		$file_tpl = str_replace('%__DIR__%', __DIR__, $file_tpl);
		$file_tpl = str_replace('%lang%', $this->languages[$lang], $file_tpl);
		return $file_tpl;
	}

	public function initialize_translation_files() {
		foreach (array_keys($this->languages) as $language) {
			$this->get_array($language);
		}
	}

	protected function replace_vars_with_params($text, $params) {
		foreach ($params as $key => $param) {
			$text = str_replace('%'.($key + 1), $param, $text);
		}
		return $text;
	}

	public function __($text, $params = []) {
		if(($translated = $this->get_translated($text, $this->get_default_language())) || $translated === '') {
			$text = $translated === '' ? $text : $translated;
			return $this->replace_vars_with_params($text, $params);
		}
		return '[ Missing Translation ] '.$this->replace_vars_with_params($text, $params);
	}

	protected function get_translated($key, $lang = self::FR) {
		$key = $this->encode_text($key);
		return isset($this->get_array($lang)[$key]) ? $this->get_array($lang)[$key] : false;
	}

	public function write_translated($key, $value = null, $lang = null) {
		$key = $this->encode_text($key);
		if(!is_null($value) && !is_null($lang)) {
			$translations = $this->get_array($lang);
			$translations[$key] = $value;
			file_put_contents($this->get_file_path($lang), $this->inject->get_service_json()->encode($translations));
		}
		else {
			foreach (array_keys($this->languages) as $language) {
				$translations = $this->get_array($language);
				if (!isset($translations[$key])) {
					$translations[$key] = '';
					if ($language === self::$default_language) {
						$translations[$key] = $this->decode_text($key);
					}
				}
				file_put_contents($this->get_file_path($language), $this->inject->get_service_json()->encode($translations));
			}
		}
	}

	public function remove_key($key) {
		$key = $this->encode_text($key);
		foreach (array_keys($this->languages) as $language) {
			$translations = $this->get_array($language);
			if (isset($translations[$key])) {
				unset($translations[$key]);
			}
			file_put_contents($this->get_file_path($language), $this->inject->get_service_json()->encode($translations));
		}
	}

	public function add_key($key, $value) {
		$key = $this->encode_text($key);
		foreach (array_keys($this->languages) as $language) {
			$translations = $this->get_array($language);
			$translations[$key] = ($language === $this->get_default_language() ? $value : '');
			file_put_contents($this->get_file_path($language), $this->inject->get_service_json()->encode($translations));
		}
	}

	public function get_array($lang = self::FR) {
		if(!is_dir(__DIR__.'/../translations')) {
			mkdir(__DIR__.'/../translations', 0777, true);
		}
		$file_path = $this->get_file_path($lang);
		if(!is_file($file_path)) {
			file_put_contents($file_path, $this->inject->get_service_json()->encode([]));
		}
		return $this->inject->get_service_json()->decode(file_get_contents($file_path), true);
	}
}