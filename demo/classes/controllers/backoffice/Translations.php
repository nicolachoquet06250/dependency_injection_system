<?php


namespace mvc_router\mvc\backoffice;


use mvc_router\mvc\Controller;
use mvc_router\mvc\views\MyView;
use mvc_router\router\Router;

class Translations extends Controller {
	/** @var \mvc_router\services\Translate $translation */
	public $translation;

	/**
	 * @route /backoffice/translations
	 * @param Router $router
	 */
	public function index(Router $router) {
		echo '<meta charset="utf-8" />';
		echo '<title>'.$this->translation->__('Liste des traductions').'</title>';

		echo '<form id="change-lang" method="get" action="">
<select name="lang" onchange="document.querySelector(\'#change-lang\').submit()">
	<option value="" disabled '.(!$router->get('lang') ? 'selected="selected"' : '').'>'.$this->translation->__('Choisir').'</option>';
		foreach ($this->translation->get_languages() as $language => $name) {
			echo '<option value="'.$language.'" '.($router->get('lang') && $router->get('lang') === $language ? 'selected="selected"' : '').'>'.$name.'</option>';
		}
		echo '</select>
</form>';

		$lang = $this->translation->get_default_language();
		if($router->get('lang')) {
			$this->translation->set_default_language($router->get('lang'));
			$lang = $this->translation->get_default_language();
		}

		echo '<script>
	function add() {
		  let key = document.querySelector(\'#key\').value;
		  let value = document.querySelector(\'#value\').value;
		  
		  let form = new FormData();
		  form.append(\'key\', key);
		  form.append(\'value\', value);
		  form.append(\'add\', 1);
		  
		  fetch(\''.array_keys($router->get_current_route())[0].'?lang='.$lang.'\', {
				method: \'POST\',
				body: form
		  }).then(() => window.location.href = \''.array_keys($router->get_current_route())[0].'?lang='.$lang.'\')
		}
</script>';
		if(!empty($router->post())) {
			if($router->post('add')) {
				$key = $router->post('key');
				$value = $router->post('value');
				$this->translation->add_key(str_replace('_', ' ', $key), $value);
			}
			else {
				foreach ($router->post() as $key => $value) {
					$this->translation->write_translated(str_replace('_', ' ', $key), $value, $lang);
				}
			}
		}

		if($router->get('key_to_remove')) {
			$this->translation->remove_key($router->get('key_to_remove'));
		}

		echo '<form action="" method="post">';
		echo '<table style="width: 100%">
	<thead>
		<tr>
			<th> '.$this->translation->__('Cléf').' </th>
			<th> '.$this->translation->__('Valeur').' </th>
			<th>'.$this->translation->__('Actions').'</th>
		</tr>
	</thead>
	<tbody>';
		foreach ($this->translation->get_array($lang) as $key => $translation) {
			echo '<tr>
		<td>'.$key.'</td>
		<td>
			<input name="'.$key.'" type="text" style="width: 100%" 
				value="'.$translation.'" placeholder="'.$this->translation->__('Traduction').'" />
		</td>
		<td>
			<input type="button" onclick="window.location.href=\'?lang='.$lang.'&key_to_remove='.$key.'\'" value="'.$this->translation->__('Supprimer').'">
		</td>
	</tr>';
		}
	echo '<tfoot>
	<tr>
		<th colspan="2"><button type="submit">'.$this->translation->__('Valider').'</button></th>
	</tr>
	<tr>
		<td>
			<input id="key" type="text" style="width: 100%" placeholder="'.$this->translation->__('Cléf').'" />
		</td>
		<td>
			<input id="value" type="text" style="width: 100%" placeholder="'.$this->translation->__('Valeur').'" />
		</td>
		<td>
			<input onclick="add()" type="button" value="'.$this->translation->__('Ajouter').'" />
		</td>
	</tr>
</tfoot>';
	echo '</tbody>
</table>';
	echo '</form>';
	}

	/**
	 * @route /translations
	 * @param Router $router
	 * @param MyView $myView
	 * @return MyView
	 */
	public function test2(Router $router, MyView $myView) {
		$this->translation->set_default_language();
		if($router->get('lang')) {
			$this->translation->set_default_language($router->get('lang'));
		}
		$myView->assign('lang', $this->translation->get_default_language());
		$myView->assign('current_route', $router->get_current_route(true));
		return $myView;
	}
}