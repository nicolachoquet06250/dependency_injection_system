<?php


namespace mvc_router\mvc\views;


use mvc_router\mvc\View;

class Translations extends View {

	/**
	 * @return array
	 */
	private function generate_all_vars_string() {
		$lang = $this->get('lang');
		$translation = $this->translate;
		$router = $this->get('router');

		$selected_default = !$lang ? 'selected="selected"' : '';
		$options = '';
		foreach ($translation->get_languages() as $language => $name) {
			$selected_option = $lang && $lang === $language ? 'selected="selected"' : '';
			$options .= "<option value='{$language}' {$selected_option}>{$name}</option>";
		}
		$url = array_keys($router->get_current_route())[0];
		$table_of_translations = '';
		foreach ($translation->get_array($lang) as $key => $_translation) {
			$key = $translation->decode_text($key);
			$key_for_value = urlencode($key);
			$table_of_translations .= "
	<tr>
		<td>{$key}</td>
		<td>
			<input name='{$key_for_value}' type='text' style='width: 95%' 
				value=\"{$_translation}\" placeholder='{$translation->__('Traduction')}' />
		</td>
		<td>
			<input type='button' onclick=\"window.location.href='{$url}?lang={$lang}&key_to_remove={$key_for_value}'\" value='{$translation->__('Supprimer')}'>
		</td>
	</tr>
";
		}
		return [$translation, $lang, $selected_default, $options, $url, $table_of_translations];
	}

	/**
	 * @return string
	 */
	public function render(): string {
		list($translation, $lang, $selected_default, $options, $url, $table_of_translations) = $this->generate_all_vars_string();
		return "<!DOCTYPE html>
	<html lang='{$lang}'>
		<head>
			<meta charset='utf-8' />
			<title>{$translation->__('Liste des traductions')}</title>
		</head>
		<body>
			<form id='change-lang' method='get' action=''>
				<select name='lang' onchange='document.querySelector(\"#change-lang\").submit()'>
					<option value='' disabled {$selected_default}>{$translation->__('Choisir')}</option>
					{$options}
				</select>
			</form>
			
			<form action='' method='post'>
				<table style='width: 100%'>
					<thead>
						<tr>
							<th> {$translation->__('Cléf')} </th>
							<th> {$translation->__('Valeur')} </th>
							<th>{$translation->__('Actions')}</th>
						</tr>
					</thead>
					<tbody>
						{$table_of_translations}
					</tbody>
					<tfoot>
						<tr>
							<th colspan='2'><button type='submit'>{$translation->__('Valider')}</button></th>
						</tr>
						<tr>
							<td>
								<input id='key' type='text' style='width: 95%' placeholder='{$translation->__('Cléf')}' />
							</td>
							<td>
								<input id='value' type='text' style='width: 95%' placeholder='{$translation->__('Valeur')}' />
							</td>
							<td>
								<input onclick='add()' type='button' value='{$translation->__('Ajouter')}' />
							</td>
						</tr>
					</tfoot>
				</table>
			</form>
		</body>
		<script>
			function add() {
				let key = document.querySelector('#key').value;
				let value = document.querySelector('#value').value;
				  
				let form = new FormData();
				form.append('key', key);
				form.append('value', value);
				form.append('add', 1);
				  
				fetch('{$url}?lang={$this->get('lang')}', {
					method: 'POST',
					body: form
				}).then(() => window.location.href = '{$url}?lang={$this->get('lang')}')
			}
		</script>
	</html>";
	}
}