<?php

/*
 * class.instellingencontent.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 * Instellingenketzerding.
 */

class InstellingenContent extends TemplateView {

	public function __construct() {
		parent::__construct();
	}

	public function getTitel() {
		return 'Websiteinstellingen';
	}

	public function view() {
		$instellingen = Instellingen::getDefaults();
		echo '<h1>Instellingen csrdelft.nl</h1>Op deze pagina kunt u diverse instellingen voor de webstek wijzigen.<br />De waarden tussen haakjes zijn de standaardwaarden.<form method="post">';
		$current = '';
		foreach ($instellingen as $key => $inst) {
			$parts = explode('_', $key);
			if ($parts[0] != $current) {
				if ($current != '') {
					echo '</fieldset><br />';
				}
				echo '<fieldset style="padding: 5px 10px;">';
				$current = $parts[0];
				echo '<legend><strong>' . ucfirst($current) . '</strong></legend>';
			}

			echo '<label style="float: left; width: 250px;" for="inst_' . $key . '">' . Instellingen::getDescription($key) . '</label>';
			if (is_array(Instellingen::getEnumOptions($key))) {
				echo '<select type="select" id="inst_' . $key . '" name="' . $key . '">';
				foreach (Instellingen::getEnumOptions($key) as $option) {
					echo '<option value="' . $option . '" ';
					if ($option == Instellingen::get($key)) {
						echo 'selected="selected"';
					}
					echo '>' . ucfirst($option) . '</option>';
				}
				echo '</select>';
			} else {
				echo ' <input type="text" id="inst_' . $key . '" name="' . $key . '" value="' . Instellingen::get($key) . '" />';
			}
			echo ' (' . ucfirst($inst) . ')<br /><br />';
		}
		echo '</fieldset><br />';
		if (LoginLid::instance()->hasPermission('P_ADMIN')) {
			echo '<input type="submit" name="save_session" value="tijdelijk opslaan in sessie"> ';
		}
		echo '<input type="submit" name="save" value="opslaan"></form>';
	}

}
