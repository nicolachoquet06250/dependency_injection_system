<?php


namespace mvc_router\mvc\views;


use mvc_router\mvc\View;

class Route extends View {
	public function render(): string {
		$lang = $this->get('lang');
		$translation = $this->translate;
		$service_route = $this->get('service_route');
		$router = $this->get('router');
		$stats = $this->get('stats');
		$_stats = $this->__('Stats');

		$options = '';
		foreach ($translation->get_languages() as $language => $name) {
			$options .= "<option value='{$language}' ".($lang && $lang === $language ? 'selected="selected"' : '').">{$name}</option>";
		}

		$stats_part = ($stats) ?
			$service_route->write_array_header($_stats, '', '', '').$service_route->write_stats_controllers().$service_route->write_stats_types()
			."<tr>
	<td colspan='4' style='height: 15px;'></td>
</tr>
<tr>
	<th colspan='4'>
		<button onclick=\"window.location.href = '/routes".($lang ? '?lang='.$lang : '')."';\">
			{$this->__('Cacher les stats')}
		</button>
	</th>
</tr>" : "<tr>
	<td colspan='4' style='height: 15px;'></td>
</tr>
<tr>
	<th colspan='4'>
		<button onclick=\"window.location.href = '/routes/stats".($lang ? '?lang='.$lang : '')."';\">
			{$this->__('Voir les stats')}
		</button>
	</th>
</tr>";

		$type = $this->__('Type');
		$controller = $this->__('Controlleur');
		$method = $this->__('Méthode');
		$route = $this->__('Route');

		return "<!DOCTYPE html>
	<html lang='{$lang}'>
		<head>
			<meta charset='utf-8' />
			<title>{$this->__('Liste des routes')}</title>
		</head>
		<body>
			<form id='change-lang' method='get' action=''>
				<select name='lang' onchange=\"document.querySelector('#change-lang').submit()\">
					<option value='' disabled ".(!$lang ? 'selected=\"selected\"' : '').">{$this->__('Choisir')}</option>
					{$options}
			    </select>
			</form>
			
			<table style='width: 100%;'>
				{$service_route->write_array_header($type, $controller, $method, $route)}
				{$service_route->write_array_lines($router)}
				{$stats_part}
			</table>
		</body>
	</html>";
	}
}