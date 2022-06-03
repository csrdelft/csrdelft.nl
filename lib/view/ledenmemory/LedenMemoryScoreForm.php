<?php

namespace CsrDelft\view\ledenmemory;

use CsrDelft\entity\LedenMemoryScore;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\TextField;

class LedenMemoryScoreForm extends Formulier {

	public function __construct(LedenMemoryScore $score) {
		parent::__construct($score, '/leden/memoryscore');

		$fields = [];
		$fields[] = new RequiredIntField('tijd', $score->tijd, null, 1);
		$fields[] = new RequiredIntField('beurten', $score->beurten, null, 1);
		$fields[] = new RequiredIntField('goed', $score->goed, null, 1);
		$fields[] = new TextField('groep', $score->groep, null);
		$fields[] = new RequiredIntField('eerlijk', $score->eerlijk, null, 0, 1);

		$this->addFields($fields);
	}

}
