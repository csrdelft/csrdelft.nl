<?php

/**
 * Instelling.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een mlt_instelling instantie beschrijft een key-value pair.
 * 
 * Bijvoorbeeld:
 *  - Standaard maaltijdprijs
 *  - Marge in verband met gasten
 *  - Corveepunten per jaar
 * 
 */
class Instelling {

	# primary key
	private $instelling_id; # string 255
	
	private $waarde; # text
	
	public function __construct($key='', $value='') {
		$this->instelling_id = $key;
		$this->setWaarde($value);
	}
	
	public function getInstellingId() {
		return $this->instelling_id;
	}
	
	public function getWaarde() {
		return $this->waarde;
	}
	
	public function setWaarde($value) {
		if ($value === null) {
			throw new Exception('Set waarde faalt: null');
		}
		$this->waarde = $value;
	}
}

?>