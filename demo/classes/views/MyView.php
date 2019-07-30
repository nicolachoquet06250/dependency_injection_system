<?php


namespace mvc_router\mvc\views;


use mvc_router\mvc\View;

class MyView extends View {
	/** @var \mvc_router\services\Translate $translate */
	public $translate;

	protected function __($text, $params = []) {
		return $this->translate->__($text, $params);
	}

	public function render() {
		$lang = $this->translate->get_default_language();

		return <<<EOT
<Doctype html>
<html lang="{$lang}">
	<head>
		<meta charset="utf-8" />
		<title>{$this->__('coucou')}</title>
	</head>
	<body>
		<h2>{$this->__('coucou')}</h2>
	</body>
</html>
EOT;
	}
}