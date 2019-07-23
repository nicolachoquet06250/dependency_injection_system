<?php


namespace mvc_router\services;


use mvc_router\router\Router;
use my_app\services\Service;

class Route extends Service {
	protected $count_types = [];

	public function get_different_controllers() {
		$routes = $this->inject->get_router()->routes();
		$count_controllers = [];
		foreach ($routes as $route => $route_details) {
			if(!isset($count_controllers[$route_details['controller']])) {
				$count_controllers[$route_details['controller']] = 0;
			}
			$count_controllers[$route_details['controller']]++;
		}
		return $count_controllers;
	}

	public function get_different_controllers_number() {
		return count($this->get_different_controllers());
	}

	public function get_different_types() {
		$routes = $this->inject->get_router()->routes();
		$count_types = [];
		foreach ($routes as $route => $route_details) {
			$route_details['type'] = ($route_details['type'] === Router::STRING ? 'string' : 'regex');
			if(!isset($count_types[$route_details['type']])) {
				$count_types[$route_details['type']] = 0;
			}
			$count_types[$route_details['type']]++;
		}
		return $count_types;
	}

	public function get_different_types_number() {
		return count($this->get_different_types());
	}

	public function get_route_number() {
		return count($this->inject->get_router()->routes());
	}

	public function write_array_lines(Router $router) {
		echo '<tbody>';
		$details = $router->get_root_route();
		if($details) $this->write_array_line('/', $details);
		foreach ( $router->routes() as $route => $details ) {
			if($route !== '/') $this->write_array_line($route, $details);
		}
		echo '</tbody>';
	}

	public function write_array_line($route, $route_detail) {
		echo '<tr>
			<td>
				' . ( $route_detail['type'] === Router::STRING ? 'String' : 'Regex' ) . '
			</td>
			<td>
				' . $route_detail['controller'] . '
			</td>
			<td>
				' . $route_detail['method'] . '
			</td>
			<td>
				'.str_replace('\\', '', $route).'
			</td>
		</tr>';
	}

	public function write_array_header(...$labels) {
		echo '<thead><tr>';
		foreach ($labels as $label) {
			echo '<th>'.$label.'</th>';
		}
		echo '</tr></thead>';
	}

	public function write_stats_controllers() {
		$this->write_array_header('Controlleurs ( nom )', 'Stat ( % )', 'Controlleurs ( nb )', 'Routes ( nb )');
		$total_of_routes = $this->get_route_number();
		echo '<tbody>';
		foreach ($this->get_different_controllers() as $controller_name => $nb_of_this_controller) {
			echo '<tr>
	<td>'.$controller_name.'</td>
	<td>'.((100 * $nb_of_this_controller) / $total_of_routes).'</td>
	<td>'.$nb_of_this_controller.'</td>
	<td>'.$total_of_routes.'</td>
</tr>';
		}
		echo '</tbody>';
	}

	public function write_stats_types() {
		$this->write_array_header('Type ( nom )', 'Stat ( % )', 'Types ( nb )', 'Routes ( nb )');
		$total_of_routes = $this->get_route_number();
		echo '<tbody>';
		foreach ($this->get_different_types() as $type_name => $nb_of_this_type) {
			echo '<tr>
	<td>'.$type_name.'</td>
	<td>'.((100 * $nb_of_this_type) / $total_of_routes).'</td>
	<td>'.$nb_of_this_type.'</td>
	<td>'.$total_of_routes.'</td>
</tr>';
		}
		echo '</tbody>';
	}
}