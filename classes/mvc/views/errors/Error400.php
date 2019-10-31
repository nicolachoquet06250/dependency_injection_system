<?php
	
	
	namespace mvc_router\mvc\views\errors;
	
	
	use mvc_router\mvc\views\Layout;
	use mvc_router\services\Error;
	
	class Error400 extends Layout {
		private $status = 400;
		
		public function after_construct() {
			parent::after_construct();
			$this->assign('title', $this->get('message'));
		}
		
		public function body(): string {
			return <<<HTML
	<h1>Error {$this->status}</h1>
	<p>{$this->get('message')}</p>
HTML;
		}
		
		public function render(): string {
			if($this->get('type') === Error::HTML) {
				return parent::render();
			}
			return $this->inject->get_service_json()->encode(
				[
					'code' => $this->status,
					'message' => $this->get('message'),
				]
			);
		}
	}