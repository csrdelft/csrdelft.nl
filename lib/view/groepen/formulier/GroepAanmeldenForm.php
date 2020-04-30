<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\AbstractGroepLid;

class GroepAanmeldenForm extends GroepBewerkenForm {

	public function __construct(
		AbstractGroepLid $lid,
		AbstractGroep $groep,
		$pasfoto = true
	) {
		parent::__construct($lid, $groep, false, new GroepAanmeldKnoppen($pasfoto));

		$this->action = $groep->getUrl() . '/ketzer/aanmelden';
		$this->css_classes[] = 'float-left';

		if ($pasfoto) {
			$this->getField()->hidden = true;
		}
	}

}
