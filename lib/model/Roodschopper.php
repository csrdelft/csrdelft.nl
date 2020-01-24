<?php

namespace CsrDelft\model;

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

use CsrDelft\common\Ini;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\Mail;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\ProfielRepository;

class Roodschopper {

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

	public static function getDefaults() {
		$return = new Roodschopper();
		$return->from = Ini::lees(Ini::EMAILS, 'fiscus');
		$return->verzenden = false;
		$return->saldogrens = -520;
		$return->onderwerp = 'U staat rood';
		$return->bericht = 'Beste LID,

Uw CiviSaldo is SALDO, dat is negatief. Inleggen met je hoofd.

Bij voorbaat dank,
h.t. Fiscus.';
		$return->bcc = LoginModel::getProfiel()->getPrimaryEmail();
		$return->uitsluiten = 'x101';
		return $return;
	}

	/**
	 * Geef een array van Lid-objecten terug van de te schoppen leden.
	 *
	 */
	public function getLeden() {
		if ($this->teschoppen === null) {
			$this->generateMails();
		}
		$leden = array();
		if (is_array($this->teschoppen)) {
			foreach ($this->teschoppen as $uid => $bericht) {
				$leden[] = ProfielRepository::get($uid);
			}
		}
		return $leden;
	}

	public function getSaldi() {
		if ($this->doelgroep == 'oudleden') {
			$status = LidStatus::getFiscaalOudlidLike();
		} else {
			$status = LidStatus::getFiscaalLidLike();
		}

		$saldi = CiviSaldoModel::instance()->find('saldo < ?', [$this->saldogrens]);

		$return = [];
		foreach ($saldi as $saldo) {
			$profiel = ProfielRepository::get($saldo->uid);

			if (!$profiel) {
				continue;
			}

			if (!in_array($profiel->status, $status)) {
				continue;
			}

			if (in_array($saldo->uid, explode(',', $this->uitsluiten))) {
				continue;
			}

			$return[] = $saldo;
		}

		return $return;
	}

	/**
	 * Voor een simulatierun uit. Er worden dan geen mails gestuurd.
	 */
	public function generateMails() {
		$this->teschoppen = [];
		foreach ($this->getSaldi() as $saldo) {
			$profiel = ProfielRepository::get($saldo->uid);

			$this->teschoppen[$saldo->uid] = [
				'onderwerp' => $this->replace($this->onderwerp, $profiel, $saldo->saldo),
				'bericht' => $this->replace($this->bericht, $profiel, $saldo->saldo),
			];
		}

		return count($this->teschoppen);
	}

	/**
	 * @param string $invoer
	 * @param Profiel $profiel
	 * @param int $saldo
	 * @return mixed
	 */
	public function replace($invoer, $profiel, $saldo) {
		return str_replace(['LID', 'SALDO'], [$profiel->getNaam('volledig'), format_bedrag($saldo)], $invoer);
	}

	/**
	 * Geef een lijstje met het onderwerp en de body van de te verzenden
	 * mails.
	 */
	public function preview() {
		if ($this->teschoppen === null) {
			$this->generateMails();
		}
		foreach ($this->teschoppen as $uid => $bericht) {
			echo '<strong>' . $bericht['onderwerp'] . '</strong><br />' . nl2br($bericht['bericht']) . '<hr />';
		}
	}

	/**
	 * Verstuurt uiteindelijk de mails.
	 */
	public function sendMails() {
		if ($this->teschoppen === null) {
			$this->generateMails();
		}

		foreach ($this->teschoppen as $uid => $bericht) {
			$profiel = ProfielRepository::get($uid);
			if (!$profiel) {
				continue;
			}
			$mail = new Mail(array($profiel->getPrimaryEmail() => $profiel->getNaam($uid, 'civitas')), $this->onderwerp, $bericht['bericht']);
			$mail->setFrom($this->from);
			if ($this->bcc) {
				$mail->addBcc([$this->bcc => $this->bcc]);
			}
			$mail->send();
		}
		exit;
	}
}
