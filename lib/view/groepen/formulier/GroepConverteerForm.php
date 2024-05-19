<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\repository\GroepRepository;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;
use Doctrine\Persistence\ManagerRegistry;

class GroepConverteerForm extends ModalForm
{
	public function __construct(
		ManagerRegistry $doctrine,
		Groep $groep,
		GroepRepository $huidig
	) {
		parent::__construct(
			$groep,
			$huidig->getUrl() . '/' . $groep->getId() . '/converteren',
			$huidig->getEntityClassName() . ' converteren',
			true
		);

		$fields = [];
		$fields[] = new GroepSoortField(
			$doctrine,
			'model',
			$huidig->getClassName(),
			'Converteren naar',
			$groep
		);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
		$this->formKnoppen->submit->icon = 'lightning';
		$this->formKnoppen->submit->label = 'Converteren';
	}

	public function getValues(): array
	{
		$values = parent::getValues();
		$values['soort'] = $this->findByName('model')->getSoort();
		return $values;
	}
}
