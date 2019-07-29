<?php


namespace mvc_router\mvc;


use mvc_router\Base;

abstract class View extends Base {
	abstract public function render();
}