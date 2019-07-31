<?php


use mvc_router\data\gesture\custom\managers\MyManager;
use mvc_router\mvc\Controller;
use mvc_router\mvc\views\BasicView;
use mvc_router\router\Router;
use mvc_router\services\Service;
use mvc_router\services\Translate;

class MyController extends Controller {
	/** @var mvc_router\services\Translate $service_translation */
	public $service_translation;

	/**
	 * @route /mon/example/
	 */
	public function index() {
		return'index';
	}

	/**
	 * @route /mon/example/2
	 * @param Service $my_service
	 */
	public function test(Service $my_service) {
		$my_service->hello();
	}

	/**
	 * @route \/([a-zA-Z]+)\/([0-9]+)
	 * @param Router $router
	 * @param        $param1
	 * @param        $param2
	 */
	public function test2(Router $router, $param1, $param2) {
		echo '<pre>';
		var_dump($router->get_current_route());
		var_dump($param1, $param2);
		echo '</pre>';
	}

	/**
	 * @route /test/lol/var
	 */
	public function toto() {
		return'hello 1';
	}

	/**
	 * @route \/([a-zA-Z0-9]+)\/hello-toi
	 * @param Service $service
	 * @param         $param1
	 */
	public function hello_toi(Service $service, $param1) {
		echo '<pre>';
		$service->hello();
		var_dump($param1);
		echo '</pre>';
	}

	/**
	 * @route /translate
	 * @param Translate                       $service_translation
	 * @param BasicView $basicView
	 * @return bool|string
	 */
	public function translate(Translate $service_translation, BasicView $basicView) {
		$sentence_p1 = $service_translation->__('Je suis %1', ['Nicolas']);
		$sentence_p2 = $service_translation->__('et toi tu es %1', ['Yann']);
		$sentence_p3 = $service_translation->__('Je suis %1 et tu es %2', ['Nicolas', 'Yann']);
		$basicView->assign('var', 'translated');
		$basicView->assign('translated', $sentence_p1.' '.$sentence_p2.'; '.$sentence_p3);
		return $basicView;
	}

	/**
	 * @route /conf
	 */
	public function test_confs() {
		return $this->html($this->service_translation->get_default_language());
	}

	/**
	 * @route /test/managers
	 * @param MyManager $manager
	 * @return string
	 * @throws Exception
	 */
	public function test_manager(MyManager $manager) {
		return '<pre>'.$this->var_dump($manager->get_entity());
	}
}