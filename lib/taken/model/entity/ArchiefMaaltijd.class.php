<?php
namespace Taken\MLT;

require_once 'agenda/agenda.class.php';

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
class ArchiefMaaltijd implements \Agendeerbaar {

	# primary key
	private $maaltijd_id; # int 11
	
	private $titel; # string 255
	private $datum; # date
	private $tijd; # time
	private $prijs; # float
	private $aanmeldingen; # text
	
	public function __construct($mid=0, $titel=null, $datum=null, $tijd=null, $prijs=null, array $aanmeldingen=null) {
		$this->maaltijd_id = (int) $mid;
		$this->titel = $titel;
		$this->datum = $datum;
		$this->tijd = $tijd;
		$this->prijs = $prijs;
		$this->aanmeldingen = '';
		foreach ($aanmeldingen as $aanmelding) {
			$this->aanmeldingen .= $aanmelding->getLidId();
			if ($aanmelding->getDoorAbonnement()) {
				$this->aanmeldingen .= '_abo';
			}
			if ($aanmelding->getDoorLidId() !== null) {
				$this->aanmeldingen .= '_'. $aanmelding->getDoorLidId();
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
		return (float) $this->prijs;
	}
	public function getAanmeldingen() {
		return $this->aanmeldingen;
	}
	public function getAantalAanmeldingen() {
		return substr_count($this->aanmeldingen, ',');
	}
	
	// Agendeerbaar ############################################################
	
	public function getBeginMoment() {
		return strtotime($this->getDatum() .' '. $this->getTijd());
	}
	public function getEindMoment() {
		return $this->getBeginMoment();
	}
	public function getBeschrijving() {
		return 'Maaltijd met '. $this->getAantalAanmeldingen() .' eters';
	}
	public function isHeledag() {
		return false;
	}
}

?>