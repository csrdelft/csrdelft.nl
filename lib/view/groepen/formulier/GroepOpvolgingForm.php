<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\view\formulier\invoervelden\AutocompleteField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\EnumSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepOpvolgingForm extends ModalForm
{
	/**
	 * @var EnumSelectField
	 */
	private $groepStatusField;
	/**
	 * @var AutocompleteField
	 */
	private $familieField;

	public function __construct(Groep $groep, $action)
	{
		parent::__construct($groep, $action, 'Opvolging instellen', true);

		$fields = [];
		$this->familieField = $fields['fam'] = new AutocompleteField(
			'familie',
			$groep->familie,
			'Familienaam'
		);
		$fields['fam']->suggestions[] = $groep->getFamilieSuggesties();

		$this->groepStatusField = $fields[] = new EnumSelectField(
			'status',
			$groep->status,
			'Status',
			GroepStatus::class
		);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

	public function getStatus(): GroepStatus
	{
		return $this->groepStatusField->getValue();
	}

	public function getFamilie(): string
	{
		return $this->familieField->getValue();
	}
}
