<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\model\ProfielModel;
use CsrDelft\view\groepen;

class GroepEmailsView extends groepen\leden\GroepTabView {

	public function getTabContent() {
		$html = '';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielModel::get($lid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				$html .= $profiel->getPrimaryEmail() . '; ';
			}
		}
		return $html;
	}

}
