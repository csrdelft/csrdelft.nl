<?php

namespace CsrDelft\view\groepen\leden;

use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\groepen;

class GroepEmailsView extends groepen\leden\GroepTabView
{

	public function getTabContent()
	{
		$html = '';
		foreach ($this->groep->getLeden() as $lid) {
			$profiel = ProfielRepository::get($lid->uid);
			if ($profiel and $profiel->getPrimaryEmail() != '') {
				$html .= $profiel->getPrimaryEmail() . '; ';
			}
		}
		return $html;
	}

}
