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
class ArchiefMaaltijd extends PersistentEntity implements Agendeerbaar {
	# primary key

	public $maaltijd_id; # int 11
	public $titel; # string 255
	public $datum; # date
	public $tijd; # time
	public $prijs; # int 11
	public $aanmeldingen; # text

	public function __construct($mid = 0, $titel = null, $datum = null, $tijd = null, $prijs = null, $aanmeldingen = array()) {
        parent::__construct();
        $this->maaltijd_id = (int)$mid;
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

	public function getPrijsFloat() {
		return (float) $this->prijs / 100.0;
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

    public function getTitel() {
        return $this->titel;
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

    protected static $table_name = 'mlt_archief';
    protected static $persistent_attributes = array(
        'maaltijd_id' => array(T::Integer, false, 'auto_increment'),
        'titel' => array(T::String),
        'datum' => array(T::Date),
        'tijd' => array(T::Time),
        'prijs' => array(T::Integer),
        'aanmeldingen' => array(T::Text),
    );

    protected static $primary_key = array('maaltijd_id');

}

?>