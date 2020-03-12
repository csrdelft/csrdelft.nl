<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\CivisaldoField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/02/2018
 */
class PinBestellingAanmakenForm extends ModalForm {
	/**
	 * @param PinTransactieMatch|null $pinTransactieMatch
	 * @throws CsrGebruikerException
	 */
	public function __construct($pinTransactieMatch = null) {
		parent::__construct([], '/fiscaat/pin/aanmaken', 'Voeg een bestelling toe.', true);

		$fields = [];
		$fields['civisaldo'] = new CivisaldoField('uid', null, 'Lid');
		$fields['civisaldo']->required = true;
		$fields['pinTransactieId'] = new RequiredIntField('pinTransactieId', $pinTransactieMatch ? $pinTransactieMatch->id : null, 'Pin Transactie Id');
		$fields['pinTransactieId']->hidden = true;

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
