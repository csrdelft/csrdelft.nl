<?php

namespace CsrDelft\view\fiscaat\pin;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingInhoudTable;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\DateTimeObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/02/2018
 */
class PinBestellingInfoForm extends ModalForm {
	/**
	 * @param PinTransactieMatch $pinTransactieMatch
	 */
	public function __construct($pinTransactieMatch) {
		parent::__construct([], '/fiscaat/pin/info', 'Match informatie', true);

		$fields = [];
		$fields['id'] = new HiddenField('id', $pinTransactieMatch->id);
		if ($pinTransactieMatch->transactie !== null) {
			$fields['pinMoment'] = new DateTimeObjectField('pinMoment', $pinTransactieMatch->transactie->datetime, 'Transactie moment');
			$fields['pinMoment']->readonly = true;
		}
		if ($pinTransactieMatch->bestelling !== null) {
			$fields['lid'] = new LidField('uid', $pinTransactieMatch->bestelling->uid, 'Account');
			$fields['lid']->readonly = true;
			$fields['moment'] = new DateTimeObjectField('moment', $pinTransactieMatch->bestelling->moment, 'Bestelling moment');
			$fields['moment']->readonly = true;
			$fields['comment'] = new TextField('comment', $pinTransactieMatch->bestelling->comment, 'Externe notitie');
		}
		$fields['intern'] = new TextareaField('intern', $pinTransactieMatch->notitie, 'Interne notitie');
		if ($pinTransactieMatch->bestelling !== null) {
			$fields[] = new CiviBestellingInhoudTable($pinTransactieMatch->bestelling);
		}

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen(null, false);
	}
}
