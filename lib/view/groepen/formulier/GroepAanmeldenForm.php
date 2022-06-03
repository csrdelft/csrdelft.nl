<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;

class GroepAanmeldenForm extends GroepBewerkenForm
{

	public function __construct(
		GroepLid $lid,
		Groep    $groep,
						 $pasfoto = true
	)
	{
		parent::__construct($lid, $groep, false, new GroepAanmeldKnoppen($pasfoto));

		$this->action = $groep->getUrl() . '/ketzer/aanmelden';
		$this->css_classes[] = 'float-start';

		if ($pasfoto) {
			$this->getField()->hidden = true;
		}
	}

}
