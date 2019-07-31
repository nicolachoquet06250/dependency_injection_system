<?php


namespace mvc_router\mvc\backoffice;


use mvc_router\mvc\Controller;
use mvc_router\mvc\views\Translation;
use mvc_router\router\Router;

class Translations extends Controller {
	/** @var \mvc_router\services\Translate $translation */
	public $translation;

	/**
	 * @route /backoffice/translations
	 * @param Router                                  $router
	 * @param \mvc_router\mvc\views\Translations $translations_view
	 * @return \mvc_router\mvc\views\Translations
	 */
	public function index(Router $router, \mvc_router\mvc\views\Translations $translations_view) {
		$lang = $this->translation->get_default_language();
		if($router->get('lang')) {
			$this->translation->set_default_language($router->get('lang'));
			$lang = $this->translation->get_default_language();
		}

		if(!empty($router->post())) {
			if($router->post('add')) {
				$key = $router->post('key');
				$value = $router->post('value');
				$this->translation->add_key(str_replace('_', ' ', $key), $value);
			}
			else {
				foreach ($router->post() as $key => $value) {
					$this->translation->write_translated(urldecode(str_replace('_', ' ', $key)), $value, $lang);
				}
			}
		}

		if($router->get('key_to_remove')) {
			$this->translation->remove_key($router->get('key_to_remove'));
		}

		$translations_view->assign('translation', $this->translation);
		$translations_view->assign('router', $router);
		$translations_view->assign('lang', $lang);

		return $translations_view;
	}

	/**
	 * @route /translations
	 * @param Router      $router
	 * @param Translation $myView
	 * @return Translation
	 */
	public function test2(Router $router, Translation $myView) {
		$this->translation->set_default_language();
		if($router->get('lang')) {
			$this->translation->set_default_language($router->get('lang'));
		}
		$myView->assign('lang', $this->translation->get_default_language());
		$myView->assign('current_route', $router->get_current_route(true));
		return $myView;
	}
}