<?php

namespace mvc_router\mvc;


use mvc_router\router\Router;

class Routes extends Controller {

	private function write_array_line($route, $route_detail) {
		echo '<tr>
			<td>
				' . ( $route_detail['type'] === Router::STRING ? 'Chaine de carctère' : 'Expression régulière' ) . '
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

	public function index(Router $router) {
		echo '<table>';
		echo '<thead><tr>
	<th>
		Type
	</th>
	<th>
		Nom du controlleur
	</th>
	<th>
		Nom de la méthode
	</th>
	<th>
		Route
	</th>
</tr></thead><tbody>';
		$details = $router->get_root_route();
		if($details) $this->write_array_line('/', $details);
		foreach ( $router->routes() as $route => $details ) {
			if($route !== '/') $this->write_array_line($route, $details);
		}
		echo '</tbody></table>';
	}
}