<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\view\formulier\getalvelden\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\RequiredLidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/02/2018
 */
class PinBestellingAanmakenForm extends ModalForm {
	/**
	 * PinBestellingAanmakenForm constructor.
	 * @param $pinTransactieId
	 * @throws CsrGebruikerException
	 */
	public function __construct($pinTransactieId = null) {
		parent::__construct([], '/fiscaat/pin/aanmaken', 'Voeg een bestelling toe.', true);

		$fields[] = new RequiredLidField('uid', null, 'Lid');
		$fields[] = new RequiredIntField('pinTransactieId', $pinTransactieId, 'Pin Transactie Id');

		$fields['btn'] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}
}
