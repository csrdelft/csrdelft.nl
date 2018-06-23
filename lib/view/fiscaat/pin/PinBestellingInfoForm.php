<?php

namespace CsrDelft\view\fiscaat\pin;
use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\view\fiscaat\bestellingen\CiviBestellingInhoudTable;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\keuzevelden\DateTimeField;
use CsrDelft\view\formulier\knoppen\ModalCloseButtons;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/02/2018
 */
class PinBestellingInfoForm extends ModalForm {
	/**
	 * @param CiviBestelling $pinBestelling
	 */
	public function __construct($pinBestelling) {
		parent::__construct([], '/fiscaat/pin/aanmaken', 'Bestelling informatie', true);

		$fields['lid'] = new LidField('uid', $pinBestelling->uid, 'Lid');
		$fields['lid']->readonly = true;
		$fields['moment'] = new DateTimeField('moment', $pinBestelling->moment, 'Moment');
		$fields['moment']->readonly = true;
		$fields[] = new CiviBestellingInhoudTable($pinBestelling);

		$this->addFields($fields);

		$this->formKnoppen = new ModalCloseButtons();
	}
}
