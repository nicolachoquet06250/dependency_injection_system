<?php
	
	
	namespace mvc_router\mvc\views;
	
	
	use mvc_router\mvc\View;
	
	class Layout extends View {
		const NONE         = 0;
		const FROM_SCRATCH = 0;
		const BOOTSTRAP    = 2;
		const SEMANTIC_UI  = 3;
		const MATERIAL_DESIGN_LIGHT = 4;
		const FONT_AWESOME = 1;
		const GLYPHICON    = 2;
		const MATERIAL_ICONS = 3;
		
		/**
		 * @return string
		 */
		public function responsive_meta_tag(): string {
			return '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />';
		}
		
		/**
		 * @param int $lib
		 * @return string
		 */
		public function font_icons( $lib = self::NONE ): string {
			switch( $lib ) {
				case self::FONT_AWESOME:
					return '
	<script defer src="https://use.fontawesome.com/releases/v5.0.13/js/solid.js"
			integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ"
			crossorigin="anonymous"></script>
	<script defer src="https://use.fontawesome.com/releases/v5.0.13/js/fontawesome.js"
			integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY"
			crossorigin="anonymous"></script>';
				case self::GLYPHICON:
					return '<link rel="stylesheet" href="https://www.glyphicons.com/css/style.css?v=12">';
				case self::MATERIAL_ICONS:
					return '<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">';
				default:
					return '';
			}
		}
		
		/**
		 * @param int $framework
		 * @param bool $jquery
		 * @param null $script_name
		 * @return string
		 */
		public function js( $framework = self::FROM_SCRATCH, $jquery = false, $script_name = null ): string {
			switch( $framework ) {
				case self::FROM_SCRATCH:
					if(substr($script_name, 0, strlen('http://')) !== 'http://' && substr($script_name, 0, strlen('https://')) !== 'https://') {
						$script_name = "/static/js/{$script_name}";
					}
					return "<script src='{$script_name}'></script>";
				case self::BOOTSTRAP:
					$jquery_str = '';
					if( $jquery ) {
						$jquery_str = "<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>";
					}
					return "
	{$jquery_str}
	<script src='https://unpkg.com/popper.js@^1'></script>
	<script src='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js'
			integrity='sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM'
			crossorigin='anonymous'></script>";
				case self::SEMANTIC_UI:
					$jquery_str = '';
					if( $jquery ) {
						$jquery_str = "<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>";
					}
					return "
	{$jquery_str}
	<script src='https://code.jquery.com/jquery-3.4.1.min.js'></script>
	<script src='https://semantic-ui.com/dist/semantic.min.js'></script>";
				case self::MATERIAL_DESIGN_LIGHT:
					return '<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>';
				default:
					return '';
			}
		}
		
		/**
		 * @param int $framework
		 * @param null $stylesheet_name
		 * @return string
		 */
		public function css( $framework = self::FROM_SCRATCH, $stylesheet_name = null ) {
			switch( $framework ) {
				case self::FROM_SCRATCH:
					if(substr($stylesheet_name, 0, strlen('http://')) !== 'http://' && substr($stylesheet_name, 0, strlen('https://')) !== 'https://') {
						$stylesheet_name = "/static/css/{$stylesheet_name}";
					}
					return "<link rel='stylesheet' href='{$stylesheet_name}' />";
				case self::BOOTSTRAP:
					return "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'
							  integrity='sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T'
							  crossorigin='anonymous' />";
				case self::SEMANTIC_UI:
					return "<link rel='stylesheet' href='https://semantic-ui.com/dist/semantic.min.css' />";
				case self::MATERIAL_DESIGN_LIGHT:
					$first_color = $this->get('material_first_theme_color') ?? 'deep_purple';
					$second_color = $this->get('material_second_theme_color') ?? 'purple';
					return "<link rel='stylesheet' href='https://code.getmdl.io/1.3.0/material.{$first_color}-{$second_color}.min.css' />";
				default:
					return '';
			}
		}
		
		/**
		 * @return string
		 */
		protected function head(): string {
			$responsive_tag = $this->get( 'is_responsive' ) ? $this->responsive_meta_tag() : '';
			$icons = $this->get( 'font_icons' ) ? $this->font_icons( $this->get( 'font_icons' ) ) : '';
			$framework = $this->get( 'framework' );
			$icon = $this->get('icon') ? "<link rel='icon' href='{$this->get('icon')}' />" : '';
			
			$stylesheets = $this->css( $framework )."\n";
			if( $this->get( 'stylesheets' ) ) {
				foreach( $this->get( 'stylesheets' ) as $stylesheet ) {
					$stylesheets .= $this->css( self::FROM_SCRATCH, $stylesheet );
				}
			}
			
			$scripts = $this->get_js();
			
			return "<meta charset='utf-8' />
	{$icon}
	<title>{$this->get('title')}</title>
	{$responsive_tag}
	{$stylesheets}
	{$icons}
	{$scripts}";
		}
		
		/**
		 * @param string $position
		 * @return string
		 */
		private function get_js($position = 'top') {
			$framework = $this->get( 'framework' )."\n";
			if($position === 'top' && $this->get('js_at_the_top') && !$this->get('js_at_the_bottom')) {
				$scripts = $this->js( $framework, $this->get( 'use_jquery' ) );
				if( $this->get( 'scripts' ) ) {
					foreach( $this->get( 'scripts' ) as $script ) {
						$scripts .= $this->js( self::FROM_SCRATCH, false, $script );
					}
				}
				return $scripts;
			}
			elseif(!$this->get('js_at_the_top') && $position === 'bottom' && $this->get('js_at_the_bottom')) {
				$scripts = $this->js( $framework, $this->get( 'use_jquery' ) )."\n";
				if( $this->get( 'scripts' ) ) {
					foreach( $this->get( 'scripts' ) as $script ) {
						$scripts .= $this->js( self::FROM_SCRATCH, false, $script );
					}
				}
				return $scripts;
			}
			
			return '';
		}
		
		/**
		 * @return string
		 */
		protected function page_header(): string {
			return '';
		}
		
		/**
		 * @return string
		 */
		protected function body(): string {
			return '';
		}
		
		/**
		 * @return string
		 */
		protected function footer(): string {
			return '';
		}
		
		/**
		 * @return string
		 */
		private final function main(): string {
			$header = $this->page_header();
			$footer = $this->footer();
			$_header = $header === '' ? '' : "<header class='{$this->get('header_class')}'>{$header}</header>";
			$_footer = $footer === '' ? '' : "<footer class='{$this->get('footer_class')}'>{$footer}</footer>";
			return "
			<!DOCTYPE html>
			<html lang='{$this->translate->get_default_language()}'>
				<head>
					{$this->head()}
				</head>
				<body>
					{$_header}
					<main class='{$this->get('main_class')}'>
						{$this->body()}
					</main>
					{$this->loader()}
					{$_footer}
					{$this->get_js('bottom')}
				</body>
			</html>
		";
		}
		
		/**
		 * @return string
		 */
		protected function loader(): string {
			return '';
		}
		
		/**
		 * @return string
		 */
		public function render(): string {
			if( !$this->get( 'title' ) ) {
				$this->assign( 'title', 'MVC Router - Documentation' );
			}
			return $this->main();
		}
	}