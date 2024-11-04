<?php

namespace CsrDelft\service;

/**
 * Roodschopperklasse.
 *
 * Stuur mensen die rood staan een schopmailtje.
 *
 * Er wordt bbcode geparsed, maar de mail wordt plaintext verzonden, dus erg veel zal daar niet
 * van overblijven. Wellicht kan er later nog een html-optie ingeklust worden.
 *
 * @deprecated
 */

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Mail;
use CsrDelft\common\Util\BedragUtil;
use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;

class Roodschopper
{
	public $saldogrens;
	public $bericht;
	public $doelgroep = 'leden';
	/**
	 * @var String onderwerp
	 */
	public $onderwerp;
	public $uitsluiten;
	public $from;
	public $bcc;
	public $teschoppen = null;

	public $verzenden;
}
