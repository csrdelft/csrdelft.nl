<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\entity\pin\PinTransactieMatch;

class PinBestellingCrediterenForm extends PinBestellingCorrectieForm
{
	protected $actie = '/fiscaat/pin/crediteer';
	protected $modalTitel = 'Crediteer bestelling.';
	protected $bestellingType = 'creditbestelling';
	protected $voltooidDeelwoord = 'Teruggedraaid';
	protected $commentNieuw = 'Terugdraaien';
	protected $uitleg = 'Er is geen transactie gevonden voor deze bestelling. Met dit formulier maak je een creditbestelling aan waarmee het CiviSaldo teruggedraaid wordt.';

	/**
	 * @param PinTransactieMatch|null $pinTransactieMatch
	 */
	public function __construct($pinTransactieMatch = null)
	{
		parent::__construct($pinTransactieMatch);
	}
}
