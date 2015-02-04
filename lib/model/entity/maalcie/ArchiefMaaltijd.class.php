<?php

/**
 * MaaltijdArchief.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een mlt_archief instantie beschrijft een individuele archiefmaaltijd als volgt:
 *  - uniek identificatienummer
 *  - titel (bijv. Donderdagmaaltijd)
 *  - datum en tijd waarop de maaltijd plaatsvond
 *  - de prijs van de maaltijd
 *  - het aantal aanmeldingen op moment van archiveren
 *  - de aanmeldingen en aanmelder in tekstvorm
 * 
 * Een gearchiveerde maaltijd is alleen-lezen en kan nooit meer uit het archief worden gehaald.
 * 
 * 
 * Zie ook Maaltijd.class.php
 * 
 */
class ArchiefMaaltijd implements Agendeerbaar {
	# primary key

	private $maaltijd_id; # int 11
	private $titel; # string 255
	private $datum; # date
	private $tijd; # time
	private $prijs; # int 11
	private $aanmeldingen; # text

	public function __construct($mid = 0, $titel = null, $datum = null, $tijd = null, $prijs = null, $aanmeldingen = array()) {
		$this->maaltijd_id = (int) $mid;
		$this->titel = $titel;
		$this->datum = $datum;
		$this->tijd = $tijd;
		$this->prijs = $prijs;
		$this->aanmeldingen = '';
		foreach ($aanmeldingen as $aanmelding) {
			if ($aanmelding->getUid() === '') {
				$this->aanmeldingen .= 'gast';
			} else {
				$this->aanmeldingen .= $aanmelding->getUid();
			}
			if ($aanmelding->getDoorAbonnement()) {
				$this->aanmeldingen .= '_abo';
			}
			if ($aanmelding->getDoorUid() !== null) {
				$this->aanmeldingen .= '_' . $aanmelding->getDoorUid();
			}
			$this->aanmeldingen .= ',';
		}
	}

	public function getMaaltijdId() {
		return (int) $this->maaltijd_id;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getDatum() {
		return $this->datum;
	}

	public function getTijd() {
		return $this->tijd;
	}

	public function getPrijs() {
		return (int) $this->prijs;
	}

	public function getPrijsFloat() {
		return (float) $this->getPrijs() / 100.0;
	}

	public function getAanmeldingen() {
		return $this->aanmeldingen;
	}

	public function getAanmeldingenArray() {
		$result = array();
		$aanmeldingen = explode(',', $this->aanmeldingen);
		foreach ($aanmeldingen as $id => $aanmelding) {
			if ($aanmelding !== '') {
				$result[$id] = explode('_', $aanmelding);
			}
		}
		return $result;
	}

	public function getAantalAanmeldingen() {
		return substr_count($this->aanmeldingen, ',');
	}

	// Agendeerbaar ############################################################

	public function getUUID() {
		return $this->maaltijd_id . '@archiefmaaltijd.csrdelft.nl';
	}

	public function getBeginMoment() {
		return strtotime($this->getDatum() . ' ' . $this->getTijd());
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 7200;
	}

	public function getBeschrijving() {
		return 'Maaltijd met ' . $this->getAantalAanmeldingen() . ' eters';
	}

	public function getLocatie() {
		return 'C.S.R. Delft';
	}

	public function getLink() {
		return '/maaltijdenbeheer/archief';
	}

	public function isHeledag() {
		return false;
	}

}

?>