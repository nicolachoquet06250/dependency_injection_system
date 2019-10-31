<?php

	namespace mvc_router\mvc\controllers\errors;
	
	use mvc_router\mvc\Controller;
	
	class Errors extends Controller {
		public $return_type;
		
		/**
		 * @route_disabled
		 */
		public function error400(string $message) {
			$view = $this->inject->get_error_400_view();
			$view->assign('message', $message);
			$view->assign('type', $this->return_type);
			
			return $view;
		}
		
		/**
		 * @route_disabled
		 */
		public function error401(string $message) {
			$view = $this->inject->get_error_401_view();
			$view->assign('message', $message);
			$view->assign('type', $this->return_type);
			
			return $view;
		}
		
		/**
		 * @route_disabled
		 */
		public function error404(string $message) {
			$view = $this->inject->get_error_404_view();
			$view->assign('message', $message);
			$view->assign('type', $this->return_type);
			
			return $view;
		}
		
		/**
		 * @route_disabled
		 */
		public function error500(string $message) {
			$view = $this->inject->get_error_500_view();
			$view->assign('message', $message);
			$view->assign('type', $this->return_type);
			
			return $view;
		}
	}