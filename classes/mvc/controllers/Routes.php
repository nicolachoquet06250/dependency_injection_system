<?php

namespace mvc_router\mvc;

use Exception;
use mvc_router\data\gesture\custom\managers\User;
use mvc_router\router\Router;
use mvc_router\services\FileSystem;
use mvc_router\services\Route;
use mvc_router\services\UrlGenerator;

class Routes extends Controller {

	/** @var \mvc_router\services\Translate $translation */
	public $translation;

	/**
	 * @route \/routes\/?(?<stats>stats)?
	 * @param views\Route $route_view
	 * @param Router      $router
	 * @param Route       $service_route
	 * @return views\Route
	 */
	public function index(views\Route $route_view, Router $router, Route $service_route) {
		$route_view->assign('service_route', $service_route);
		$route_view->assign('router', $router);
		$route_view->assign('stats', $this->param('stats') !== null);

		if($lang = $router->get('lang')) $this->translation->set_default_language($lang);

		$route_view->assign('lang', $this->translation->get_default_language());

		return $route_view;
	}

	/**
	 * @route /routes/url_generator
	 * @param UrlGenerator $urlGenerator
	 * @param FileSystem   $fileSystem
	 * @return false|string
	 * @throws Exception
	 */
	public function url_generator(UrlGenerator $urlGenerator, FileSystem $fileSystem) {
		$before_links = '';
		$link_with_stats = '<a href="'.$urlGenerator->get_url_from_ctrl_and_method($this, 'index', 'stats').'">
	Aller aux routes avec stats
</a>';
		$link_without_stats = '<a href="'.$urlGenerator->get_url_from_ctrl_and_method($this, 'index').'">
	Aller aux routes sans stats
</a>';
		$link_refresh = '<a href="'.$urlGenerator->get_url_from_ctrl_and_method($this, 'url_generator').'">
	Rafraichir
</a>';
		return $before_links.'<br>'.$link_with_stats.'<br>'.$link_without_stats.'<br>'.$link_refresh;
	}
	
	/**
	 * @param User $user_manager
	 * @return false|string
	 */
	public function test_managers(User $user_manager) {
		$users = $user_manager->get_all_from_id(1);
		return $this->var_dump($users);
	}
}