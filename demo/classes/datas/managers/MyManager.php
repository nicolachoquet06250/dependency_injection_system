<?php


namespace mvc_router\data\gesture\custom\managers;


use mvc_router\data\gesture\Manager;

/**
 * Class Manager
 *
 * @method array get_id_test_lol_from_id(int $id)
 *
 * @package mvc_router\data\gesture\custom
 */

class MyManager extends Manager {
	protected $entity_class = 'mvc_router\data\gesture\custom\entities\MyManager';
}