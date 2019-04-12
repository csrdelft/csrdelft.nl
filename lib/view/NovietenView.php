<?php

namespace CsrDelft\view;

/**
 * Voor de NovCie, zorgt ervoor dat novieten bekeken kunnen worden als dat afgeschermd is op de rest van de stek.
 */
class NovietenView implements View {

	private $novieten;

	public function __construct($novieten) {
		$this->novieten = $novieten;
	}

	public function view() {
		echo '<table class="table"><tr><th>UID</th><th>Voornaam</th><th>Tussenvoegsel</th><th>Achternaam</th><th>Mobiel</th><th>Studie</th></tr>';
		foreach ($this->novieten as $item) {
			echo <<<NOV
<tr>
<td><a href="/profiel/{$item['uid']}">{$item['uid']}</a></td>
<td>{$item['voornaam']}</td>
<td>{$item['tussenvoegsel']}</td>
<td>{$item['achternaam']}</td>
<td>{$item['mobiel']}</td>
<td>{$item['studie']}</td>
</tr>
NOV;
			echo '</table>';
		}
	}

	public function getTitel() {
		return "Novieten";
	}

	public function getBreadcrumbs() {
		return "Novieten";
	}

	public function getModel() {
		return null;
	}
}
