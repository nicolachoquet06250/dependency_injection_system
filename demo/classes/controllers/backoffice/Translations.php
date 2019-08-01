<?php


namespace mvc_router\mvc\backoffice;


use Exception;
use mvc_router\mvc\Controller;
use mvc_router\mvc\views\Translation;
use mvc_router\router\Router;
use mvc_router\services\Logger;

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
		$translations_view->assign('regenerate_translation', false);
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

		$translations_view->assign('router', $router);
		$translations_view->assign('lang', $lang);

		return $translations_view;
	}

	/**
	 * @route /backoffice/translations/regenerate
	 * @param Logger $logger
	 * @param Router $router
	 * @return string
	 * @throws Exception
	 */
	public function regenerate_translations(Logger $logger, Router $router) {
		if($router->post('regenerate')) {
			$logger->types(Logger::CONSOLE);
			$logger->separator('--------------------------------------------------------------');

			$logger->log('Command: php exe.php generate:translation');
			$logger->log_separator();
			$logger->log($this->inject->get_commands()->run('generate:translations'));
			$logger->log_separator();

			$reload_page = $this->translation->__('Recharger la page');

			return "<input type='button' value='{$reload_page}' onclick='window.location.reload()' />";
		}
		return '';
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