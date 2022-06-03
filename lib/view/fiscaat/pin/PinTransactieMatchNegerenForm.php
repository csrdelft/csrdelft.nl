<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author J.P.T. Nederveen <ik@tim365.nl>
 * @since 27/06/2020
 */
class PinTransactieMatchNegerenForm extends ModalForm
{
	/**
	 * PinTransactieMatchNegerenForm constructor.
	 * @param string[] $matches
	 */
	public function __construct($matches)
	{
		parent::__construct(new PinTransactieMatch(), '/fiscaat/pin/negeer', 'Negeer matches', true);

		$fields = [];
		$fields[] = new HtmlComment('Voeg de reden van negeren toe (voor intern gebruik)');
		$fields['intern'] = new TextareaField("intern", "", "Interne opmerking");
		$fields['ids'] = new HiddenField('ids', implode(',', $matches));

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen(null, false);
	}
}
