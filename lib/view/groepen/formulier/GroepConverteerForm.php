<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepConverteerForm extends ModalForm {

	public function __construct(
		AbstractGroep $groep,
		AbstractGroepenModel $huidig
	) {
		parent::__construct($groep, $huidig->getUrl() . 'converteren', $huidig::ORM . ' converteren', true);

		$fields[] = new GroepSoortField('model', get_class($huidig), 'Converteren naar', $groep);

		$fields['btn'] = new FormDefaultKnoppen();
		$fields['btn']->submit->icon = 'lightning';
		$fields['btn']->submit->label = 'Converteren';

		$this->addFields($fields);
	}

	public function getValues() {
		$values = parent::getValues();
		$values['soort'] = $this->findByName('model')->getSoort();
		return $values;
	}

}
