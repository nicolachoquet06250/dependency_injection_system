<?php


use mvc_router\mvc\Controller;
use mvc_router\router\Router;
use mvc_router\services\Service;
use mvc_router\services\Translate;

class MyController extends Controller {
	/** @var mvc_router\services\Translate $service_translation */
	public $service_translation;

	/** @var mvc_router\confs\custom\Mysql $mysqli_conf */
	public $mysqli_conf;

	/**
	 * @route /mon/example/
	 */
	public function index() {
		var_dump('index');
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
		var_dump('hello 1');
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
	 * @param Translate $service_translation
	 * @return bool|string
	 */
	public function translate(Translate $service_translation) {
		$sentence_p1 = $service_translation->__('Je suis %1', ['Nicolas']);
		$sentence_p2 = $service_translation->__('et toi tu es %1', ['Yann']);
		$sentence_p3 = $service_translation->__('Je suis %1 et tu es %2', ['Nicolas', 'Yann']);
		return $sentence_p1.' '.$sentence_p2.'; '.$sentence_p3;
	}

	/**
	 * @route /conf
	 */
	public function test_confs() {
		return $this->html($this->service_translation->get_default_language().'<br>'.$this->mysqli_conf->user);
	}
}