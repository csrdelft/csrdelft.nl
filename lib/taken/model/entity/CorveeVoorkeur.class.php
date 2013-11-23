<?php
namespace Taken\CRV;
/**
 * CorveeVoorkeur.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een crv_voorkeur instantie beschrijft een voorkeur van een lid om een periodieke taak uit te voeren.
 * 
 * 
 * Zie ook CorveeRepetitie.class.php
 * 
 */
class CorveeVoorkeur {

	# shared primary key
	private $crv_repetitie_id; # foreign key crv_repetitie.id
	private $lid_id; # foreign key lid.uid
	
	private $corvee_repetitie;
	private $van_lid;
	
	public function __construct($mrid=0, $uid='') {
		$this->crv_repetitie_id = (int) $mrid;
		$this->lid_id = $uid;
	}
	
	public function getCorveeRepetitieId() {
		return (int) $this->crv_repetitie_id;
	}
	public function getLidId() {
		return $this->lid_id;
	}
	
	public function getCorveeRepetitie() {
		return $this->corvee_repetitie;
	}
	public function getLid() {
		$uid = $this->van_lid;
		$lid = \LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		return $lid;
	}
	
	public function setCorveeRepetitie(CorveeRepetitie $repetitie) {
		$this->corvee_repetitie = $repetitie;
	}
	public function setVanLid($uid) {
		$this->van_lid = $uid;
	}
}

?>