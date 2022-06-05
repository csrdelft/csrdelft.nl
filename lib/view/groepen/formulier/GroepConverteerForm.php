<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\repository\GroepRepository;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepConverteerForm extends ModalForm
{
	public function __construct(Groep $groep, GroepRepository $huidig)
	{
		parent::__construct(
			$groep,
			$huidig->getUrl() . '/converteren',
			$huidig->entityClass . ' converteren',
			true
		);

		$fields = [];
		$fields[] = new GroepSoortField(
			'model',
			get_class($huidig),
			'Converteren naar',
			$groep
		);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
		$this->formKnoppen->submit->icon = 'lightning';
		$this->formKnoppen->submit->label = 'Converteren';
	}

	public function getValues()
	{
		$values = parent::getValues();
		$values['soort'] = $this->findByName('model')->getSoort();
		return $values;
	}
}
