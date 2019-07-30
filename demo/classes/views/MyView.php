<?php


namespace mvc_router\mvc\views;


use mvc_router\mvc\View;
use mvc_router\services\Translate;

class MyView extends View {
	public function render() {
		$lang = $this->translate->get_default_language();
		$current_route = $this->inject->get_router()->get_current_route();
		$current_route = array_keys($current_route)[0];

		return <<<EOT
<!Doctype html>
<html lang="{$lang}">
	<head>
		<title>{$this->__('coucou')}</title>
		{$this->materializeCssV1Top(true)}
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col s12">
					<div class="input-field center-align">
						<input type="text" value="{$lang}" id="lang" placeholder="Language" />
						<label for="lang">Language</label>
						<a class="waves-effect waves-light btn" onclick="window.location.href = '{$current_route}?lang=' + document.querySelector('#lang').value">Changer</a>
					</div>
				</div>
			</div>
		</div>
		<hr />
		<h2>{$this->__('coucou')}</h2>
		materialize: {$this->is_use_materialize()}<br>
		bootstrap: {$this->is_use_bootstrap()}<br>
		rien: {$this->is_use_none()}
	</body>
</html>
EOT;
	}
}