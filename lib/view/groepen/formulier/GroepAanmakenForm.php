<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepAanmakenForm extends ModalForm {

	public function __construct(
		AbstractGroepenModel $huidig,
		$soort = null
	) {
		$groep = $huidig->nieuw($soort);
		parent::__construct($groep, $huidig->getUrl() . 'nieuw', 'Nieuwe ketzer aanmaken');
		$this->css_classes[] = 'redirect';

		$default = get_class($huidig);
		if (property_exists($groep, 'soort')) {
			$default .= '_' . $groep->soort;
		}
		$fields[] = new KetzerSoortField('model', $default, null, $groep);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen(null, false);
		$this->formKnoppen->submit->icon = 'add';
		$this->formKnoppen->submit->label = 'Aanmaken';
	}

	public function getValues() {
		$return = array();
		$value = $this->findByName('model')->getValue();
		$values = explode('_', $value, 2);
		$return['model'] = $values[0];
		if (isset($values[1])) {
			$return['soort'] = $values[1];
		} else {
			$return['soort'] = null;
		}
		return $return;
	}

}
