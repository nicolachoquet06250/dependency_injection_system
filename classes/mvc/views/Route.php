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
		$_stats = $translation->__('Stats');

		$options = '';
		foreach ($translation->get_languages() as $language => $name) {
			$options .= "<option value='{$language}' ".($lang && $lang === $language ? 'selected="selected"' : '').">{$name}</option>";
		}

		$stats_part = ($router->get('stats') === true || $router->get('stats') === 1 || !is_null($stats)) ?
			$service_route->write_array_header($_stats, '', '', '').$service_route->write_stats_controllers().$service_route->write_stats_types()
			."<tr>
	<td colspan='4' style='height: 15px;'></td>
</tr>
<tr>
	<th colspan='4'>
		<button onclick=\"window.location.href = '/routes".($lang ? '?lang='.$lang : '')."';\">
			{$translation->__('Cacher les stats')}
		</button>
	</th>
</tr>" : "<tr>
	<td colspan='4' style='height: 15px;'></td>
</tr>
<tr>
	<th colspan='4'>
		<button onclick=\"window.location.href = '/routes/stats".($lang ? '?lang='.$lang : '')."';\">
			{$translation->__('Voir les stats')}
		</button>
	</th>
</tr>";

		return "<!DOCTYPE html>
	<html lang='{$lang}'>
		<head>
			<meta charset='utf-8' />
			<title>{$translation->__('Liste des routes')}</title>
		</head>
		<body>
			<form id='change-lang' method='get' action=''>
				<select name='lang' onchange=\"document.querySelector('#change-lang').submit()\">
					<option value='' disabled ".(!$lang ? 'selected=\"selected\"' : '').">{$translation->__('Choisir')}</option>
					{$options}
			    </select>
			</form>
			
			<table style='width: 100%;'>
				{$service_route->write_array_header($translation->__('Type'), $translation->__('Controlleur'), 
					$translation->__('Méthode'), $translation->__('Route'))}
				{$service_route->write_array_lines($router)}
				{$stats_part}
			</table>
		</body>
	</html>";
	}
}