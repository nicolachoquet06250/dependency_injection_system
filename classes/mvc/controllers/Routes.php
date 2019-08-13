<?php

namespace mvc_router\mvc;

use mvc_router\router\Router;
use mvc_router\services\Route;
use mvc_router\services\UrlGenerator;

class Routes extends Controller {

	/** @var \mvc_router\services\Translate $translation */
	public $translation;

	/**
	 * @route \/routes\/?(stats)?
	 * @param views\Route $route_view
	 * @param Router      $router
	 * @param Route       $service_route
	 * @param string|null $stats
	 * @return views\Route
	 */
	public function index(views\Route $route_view, Router $router, Route $service_route, $stats = null) {
		$route_view->assign('translation', $this->translation);
		$route_view->assign('service_route', $service_route);
		$route_view->assign('router', $router);
		$route_view->assign('stats', $router->get('stats') === true || $router->get('stats') === 1 || !is_null($stats));

		if($lang = $router->get('lang')) {
			$this->translation->set_default_language($lang);
		}

		$route_view->assign('lang', $this->translation->get_default_language());

		return $route_view;
	}

	/**
	 * @route /routes/url_generator
	 * @param UrlGenerator $urlGenerator
	 * @return false|string
	 */
	public function url_generator(UrlGenerator $urlGenerator) {
		$link_with_stats = '<a href="'.$urlGenerator->get_current_protocol().
						   $urlGenerator->get_base_url().
						   $urlGenerator->get_url_from_ctrl_and_method($this, 'index', 'stats').'">Aller aux routes avec stats</a>';
		$link_without_stats = '<a href="'.$urlGenerator->get_current_protocol().
						   $urlGenerator->get_base_url().
						   $urlGenerator->get_url_from_ctrl_and_method($this, 'index').'">Aller aux routes sans stats</a>';
		return $link_with_stats.'<br>'.$link_without_stats;
	}
}