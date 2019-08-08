<?php


namespace mvc_router\mvc\models;


use mvc_router\mvc\Model;

class User extends Model {

	protected $table = '';

	protected function after_construct() {
		parent::after_construct();
	}

	public function query($query) {
		// SELECT * FROM [table] WHERE toto = 10
		$query = str_replace('[table]', $this->table, $query);
		$this->mysqli->query($query);
	}

	public function get(array $fields = [], array $where = []) {
		$fields = empty($fields) ? '*' : '`'.implode('`, `', $fields).'`';
		$_where = empty($where) ? '' : ' WHERE ';
		foreach ($where as $where_line) {
			if($where_line['type'] === 'operator') {
				$_where .= ' '.$where_line['value'].' ';
			}
			elseif ($where_line['type'] === 'cond') {
				if(!isset($where_line['operator'])) {
					$where_line['operator'] = '=';
				}
				$_where .= $where_line['key'].$where_line['operator'].$where_line['value'];
			}
		}
		$this->query('SELECT '.$fields.' FROM [table] WHERE '.$_where);
	}

}