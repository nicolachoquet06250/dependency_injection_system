<?php


namespace mvc_router\services;


use mvc_router\router\Router;

/**
 * @package mvc_router\services
 */
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
		$str = '<tbody>';
		$details = $router->get_root_route();
		if($details) $str .= $this->write_array_line('/', $details);
		foreach ( $router->routes() as $route => $details ) {
			if($route !== '/') $str .= $this->write_array_line($route, $details);
		}
		$str .= '</tbody>';
		return $str;
	}

	public function write_array_line($route, $route_detail) {
		if($route_detail['http_method'] === Router::HTTP_POST) {
			return '';
		}
		return '<tr>
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
				'.($route_detail['type'] === Router::STRING
					? '<a href="'.str_replace('\\', '', $route).'">' : '')
			   					.str_replace('\\', '', htmlspecialchars($route))
			   					.($route_detail['type'] === Router::STRING ? '</a>' : '').'
			</td>
		</tr>';
	}

	public function write_array_header(...$labels) {
		$str = '<thead><tr>';
		foreach ($labels as $label) {
			$str .= '<th>'.$label.'</th>';
		}
		$str .= '</tr></thead>';
		return $str;
	}

	public function write_stats_controllers() {
		$service_translation = $this->inject->get_service_translation();
		$str = $this->write_array_header(
			$service_translation->__('Controlleurs ( nom )'),
			$service_translation->__('Stat ( % )'),
			$service_translation->__('Controlleurs ( nb )'),
			$service_translation->__('Routes ( nb )')
		);
		$total_of_routes = $this->get_route_number();
		$str .= '<tbody>';
		foreach ($this->get_different_controllers() as $controller_name => $nb_of_this_controller) {
			$str .= '<tr>
	<td>'.$controller_name.'</td>
	<td>'.((100 * $nb_of_this_controller) / $total_of_routes).'</td>
	<td>'.$nb_of_this_controller.'</td>
	<td>'.$total_of_routes.'</td>
</tr>';
		}
		$str .= '</tbody>';
		return $str;
	}

	public function write_stats_types() {
		$service_translation = $this->inject->get_service_translation();
		$str = $this->write_array_header(
			$service_translation->__('Type ( nom )'),
			$service_translation->__('Stat ( % )'),
			$service_translation->__('Types ( nb )'),
			$service_translation->__('Routes ( nb )')
		);
		$total_of_routes = $this->get_route_number();
		$str .= '<tbody>';
		foreach ($this->get_different_types() as $type_name => $nb_of_this_type) {
			$str .= '<tr>
	<td>'.$type_name.'</td>
	<td>'.((100 * $nb_of_this_type) / $total_of_routes).'</td>
	<td>'.$nb_of_this_type.'</td>
	<td>'.$total_of_routes.'</td>
</tr>';
		}
		$str .= '</tbody>';
		return $str;
	}
}