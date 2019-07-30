<?php


namespace mvc_router\mvc\views;


use mvc_router\mvc\View;

class BasicView extends View {
	public function render() {
		return $this->get($this->get('var'));
	}
}