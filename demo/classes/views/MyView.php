<?php


namespace mvc_router\mvc\views;


use mvc_router\mvc\View;

class MyView extends View {
	/** @var \mvc_router\services\Translate $translate */
	public $translate;

	public function render() {
		$lang = $this->translate->get_default_language();
		return '<html lang="'.$lang.'">
	<title>coucou</title>
</html>';
	}
}