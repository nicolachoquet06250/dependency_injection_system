<?php


namespace mvc_router\services;


use ReflectionException;

/**
 * Crée et lance les triggers
 *
 * @package mvc_router\services
 */
class TriggerRegisterer extends Service {
	/** @var \mvc_router\services\Trigger $triggers */
	public $triggers;

	/**
	 * @throws ReflectionException
	 */
	public function initialize() {
		$this->triggers->register('trigger_test', 'mvc_router\services\Trigger::trigger_test');
	}
}