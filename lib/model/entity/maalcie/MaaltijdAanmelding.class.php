<?php

/**
 * MaaltijdAanmelding.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een mlt_aanmelding instantie beschrijft een individuele aanmelding van een lid voor een maaltijd als volgt:
 *  - voor welke maaltijd deze aanmelding is
 *  - van welk lid deze aanmelding is
 *  - het aantal gasten dat het lid aanmeldt
 *  - opmerkingen met betrekking tot de aangemelde gasten (bijv. allergien)
 *  - of de aanmelding door een abonnement is aangemaakt en zo ja voor welke maaltijd-repetitie
 *  - door welk lid deze aanmelding is gemaakt (bijv. als een lid door een ander lid wordt aangemeld, of door de fiscus achteraf, anders gelijk aan aanmelding lid id)
 *  - wanneer de aanmelding voor het laatst is aangepast
 * 
 * Een aanmelding wordt verwijderd als een lid zich afmeldt of het abonnement uitschakelt dat deze aanmelding heeft aangemaakt, BEHOUDENS gesloten maaltijden.
 * Een aanmelding blijft verder altijd bestaan, zelfs als de maaltijd wordt aangemerkt als verwijderd. Dus ook als de aanmelding NIET door een abonnement is gemaakt en het abonnement voor deze maaltijd-repetitie uitgeschakeld wordt.
 * Een lid wordt automatisch aangemeld bij het creeren van een repetitie-maaltijd als er een abonnement op die maaltijd-repetie is ingesteld voor dat lid.
 * Het is mogelijk dat door de fiscus een aanmelding wordt aangemaakt (of verwijderd), zelfs na het sluiten van de maaltijd.
 * 
 * 
 * Zie ook MaaltijdAbonnement.class.php
 * 
 */
class MaaltijdAanmelding extends PersistentEntity  {
	# shared primary key

	public $maaltijd_id; # foreign key maaltijd.id
	public $uid; # foreign key lid.uid
	public $aantal_gasten; # int 11
	public $gasten_eetwens; # string 255
	public $door_abonnement; # foreign key mlt_repetitie.id
	public $door_uid; # foreign key lid.uid
	public $laatst_gewijzigd; # datetime
	public $maaltijd;

	public function __construct($mid = 0, $uid = '', $gasten = 0, $opmerking = '', $door_abo = null, $door_uid = null, $wanneer = '') {
        parent::__construct();
		$this->maaltijd_id = (int) $mid;
		$this->uid = $uid;
		$this->aantal_gasten = $gasten;
		$this->gasten_eetwens = $opmerking;
		$this->door_abonnement = $door_abo;
		$this->door_uid = $door_uid;
		$this->laatst_gewijzigd = $wanneer;
	}

	/**
	 * Haal het MaalCie saldo op van het lid van deze aanmelding.
	 * 
	 * @return float if lid exists, false otherwise
	 */
	public function getSaldo() {
		return ProfielModel::get($this->uid)->maalcieSaldo;
	}

	/**
	 * Bereken of het saldo toereikend is voor de prijs van de maaltijd.
	 * 
	 * 3: saldo meer dan genoeg
	 * 
	 * 2: saldo precies genoeg
	 * 
	 * 1: saldo positief maar te weinig
	 * 
	 * 0: saldo nul
	 * 
	 * -1: saldo negatief
	 * 
	 * @return int
	 */
	public function getSaldoStatus() {
		$saldo = $this->getSaldo();
		$prijs = $this->maaltijd->getPrijsFloat();

		if ($saldo > $prijs) { // saldo meer dan genoeg
			return 3;
		} elseif ($saldo > $prijs - 0.004) { // saldo precies genoeg
			return 2;
		} elseif ($saldo > 0.004) { // saldo positief maar te weinig
			return 1;
		} elseif ($saldo > -0.004) { // saldo nul
			return 0;
		} else {
			return -1; // saldo negatief
		}
	}

	/**
	 * Melding voor saldo status.
	 * 
	 * @return String
	 */
	public function getSaldoMelding() {
		$status = $this->getSaldoStatus();
		$prijs = sprintf('%.2f', $this->maaltijd->getPrijsFloat());
		switch ($status) {
			case 3: return 'ok';
			case 2: return $prijs;
			case 1: return '&lt; ' . $prijs;
			case 0: return '0';
			case -1: return '&lt; 0';
		}
	}

    protected static $table_name = 'mlt_aanmeldingen';
    protected static $persistent_attributes = array(
        'maaltijd_id' => array(T::Integer),
        'uid' => array(T::UID),
        'aantal_gasten' => array(T::Integer),
        'gasten_eetwens' => array(T::String),
        'door_abonnement' => array(T::Integer),
        'door_uid' => array(T::UID),
        'laatst_gewijzigd' => array(T::Date),
    );

    protected static $primary_key = array('maaltijd_id', 'uid');

}

?>