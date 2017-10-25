<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\model\ProfielModel;
use CsrDelft\view\groepen;

class GroepEetwensView extends groepen\leden\GroepTabView {

	public function getTabContent() {
		$html = '<table class="groep-lijst"><tbody>';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->eetwens != '') {
				$html .= '<tr><td>' . $profiel->getLink() . '</td><td>' . $profiel->eetwens . '</td></tr>';
			}
		}
		return $html . '</tbody></table>';
	}

}
