<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\entity\pin\PinTransactieMatch;

class PinBestellingVeranderenForm extends PinBestellingCorrectieForm {
	protected $actie = '/fiscaat/pin/update';
	protected $modalTitel = 'Corrigeer bestelling.';
	protected $bestellingType = 'corrigerende bestelling';
	protected $voltooidDeelwoord = 'Gecorrigeerd';
	protected $commentNieuw = 'Correctie';
	protected $uitleg = 'Het bedrag van deze transactie komt niet overeen met de bestelling. Maak hieronder een corrigerende bestelling aan.';

	/**
	 * @param PinTransactieMatch|null $pinTransactieMatch
	 */
	public function __construct($pinTransactieMatch = null) {
		parent::__construct($pinTransactieMatch);
	}
}
