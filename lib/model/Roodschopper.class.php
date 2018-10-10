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
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\MijnSqli;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\CsrBB;

class Roodschopper {

	private $cie = 'soccie';
	private $saldogrens;
	private $bericht;
	private $doelgroep = 'leden';
	/**
	 * @var String onderwerp
	 */
	private $onderwerp;
	private $uitsluiten = array();
	private $from;
	private $bcc;
	private $teschoppen = null;

	public function __construct($cie, $saldogrens, $onderwerp, $bericht) {
		if (!in_array($cie, array('maalcie', 'soccie'))) {
			throw new CsrGebruikerException('Ongeldige commissie');
		}
		$this->cie = $cie;
		//er wordt in roodschopper.php -abs($saldogrens) gedaan, dus dat dit voorkomt
		//is onwaarschijnlijk.
		if ($saldogrens > 0) {
			throw new CsrGebruikerException('Saldogrens moet beneden nul zijn');
		}

		$this->saldogrens = $saldogrens;
		$this->onderwerp = htmlspecialchars($onderwerp);
		$this->bericht = htmlspecialchars($bericht);

		if ($this->cie == 'maalcie') {
			$this->from = 'maalcie-fiscus@csrdelft.nl';
		} else {
			$this->from = $this->cie . '@csrdelft.nl';
		}
	}

	public static function getDefaults() {
		$cie = 'soccie';
		$naam = 'SocCie';
		if (LoginModel::mag('commissie:MaalCie')) {
			$cie = 'maalcie';
			$naam = 'MaalCie';
		}
		$bericht = 'Beste LID,
Uw saldo bij de ' . $naam . ' is E SALDO, dat is negatief. Inleggen met je hoofd.

Bij voorbaat dank,
h.t. Fiscus.';

		$return = new Roodschopper($cie, -5.2, 'U staat rood', $bericht);
		$return->setBcc(array(LoginModel::getProfiel()->getNaam() => LoginModel::getProfiel()->getPrimaryEmail()));
		$return->setUitgesloten('x101');
		return $return;
	}

	public function getCommissie() {
		return $this->cie;
	}

	public function getBcc() {
		return $this->bcc;
	}

	public function setBcc(array $bcc) {
		$this->bcc = $bcc;
	}

	public function getFrom() {
		return $this->from;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function getSaldogrens() {
		return $this->saldogrens;
	}

	public function getUitgesloten() {
		return implode(',', $this->uitsluiten);
	}

	public function setUitgesloten($uids) {
		if (is_array($uids)) {
			$this->uitsluiten = $uids;
		} elseif (AccountModel::isValidUid($uids)) {
			$this->uitsluiten[] = $uids;
		} else {
			$this->uitsluiten = explode(',', $uids);
		}
	}

	public function getDoelgroep() {
		return $this->doelgroep;
	}

	public function setDoelgroep($doelgroep) {
		$this->doelgroep = $doelgroep;
	}

	public function getOnderwerp() {
		return $this->onderwerp;
	}

	public function getBericht() {
		return $this->bericht;
	}

	/**
	 * Voor een simulatierun uit. Er worden dan geen mails gestuurd.
	 */
	public function simulate() {
		$db = MijnSqli::instance();
		if ($this->doelgroep == 'oudleden') {
			$where = "status='S_OUDLID' OR status='S_ERELID' OR status='S_NOBODY' OR status='S_EXLID'";
		} else {
			$where = "status='S_LID' OR status='S_NOVIET' OR status='S_GASTLID' OR status='S_KRINGEL'";
		}
		$query = "
			SELECT uid, " . $this->cie . "Saldo AS saldo
			FROM profielen
			WHERE " . $this->cie . "Saldo<" . str_replace(',', '.', $this->saldogrens) . "
			 AND (" . $where . ")
			ORDER BY achternaam, voornaam;";

		$data = $db->query2array($query);

		$bericht = CsrBB::parse($this->bericht);

		$this->teschoppen = array();
		if (is_array($data)) {
			foreach ($data as $profielsaldo) {
				//als het uid in $this->uitsluiten staat sturen we geen mails.
				if (in_array($profielsaldo['uid'], $this->uitsluiten)) {
					continue;
				}
				$this->teschoppen[$profielsaldo['uid']] = array(
					'onderwerp' => $this->replace($this->onderwerp, $profielsaldo['uid'], $profielsaldo['saldo']),
					'bericht' => $this->replace($this->bericht, $profielsaldo['uid'], $profielsaldo['saldo']));
			}
		}

		return count($this->teschoppen);
	}

	//'compile' template.
	public function replace($invoer, $uid, $saldo) {
		$profiel = ProfielModel::get($uid);
		$saldo = number_format($saldo, 2, '.', '');
		return str_replace(array('LID', 'SALDO'), array($profiel->getNaam(), $saldo), $invoer);
	}

	/**
	 * Geef een array van Lid-objecten terug van de te schoppen leden.
	 *
	 */
	public function getLeden() {
		if ($this->teschoppen === null) {
			$this->simulate();
		}
		$leden = array();
		if (is_array($this->teschoppen)) {
			foreach ($this->teschoppen as $uid => $bericht) {
				$leden[] = ProfielModel::get($uid);
			}
		}
		return $leden;
	}

	/**
	 * Geef een lijstje met het onderwerp en de body van de te verzenden
	 * mails.
	 */
	public function preview() {
		if ($this->teschoppen === null) {
			$this->simulate();
		}
		foreach ($this->teschoppen as $uid => $bericht) {
			echo '<strong>' . $bericht['onderwerp'] . '</strong><br /' . nl2br($bericht['bericht']) . '<hr />';
		}
	}

	/**
	 * Verstuurt uiteindelijk de mails.
	 */
	public function doit() {
		if ($this->teschoppen === null) {
			$this->simulate();
		}

		foreach ($this->teschoppen as $uid => $bericht) {
			$profiel = ProfielModel::get($uid);
			if (!$profiel) {
				continue;
			}
			$mail = new Mail(array($profiel->getPrimaryEmail() => $profiel->getNaam($uid, 'civitas')), $this->getOnderwerp(), $bericht['bericht']);
			$mail->setFrom($this->getFrom());
			$mail->addBcc($this->getBcc());
			$mail->send();
		}
		exit;
	}

}
