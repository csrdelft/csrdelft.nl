<?php

/**
 * KwalificatiesModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class KwalificatiesModel extends CachedPersistenceModel {

	const orm = 'CorveeKwalificatie';

	protected static $instance;

	protected function __construct() {
		parent::__construct('maalcie/');
	}

	/**
	 * Lazy loading of corveefunctie.
	 * 
	 * @return CorveeKwalificatie[]
	 */
	public function getAlleKwalificaties() {
		return group_by('functie_id', $this->prefetch());
	}

	public function getKwalificatiesVoorFunctie($fid) {
		return $this->find('functie_id = ?', array($fid))->fetchAll();
	}

	/**
	 * Eager loading of corveefuncties.
	 * 
	 * @param string $uid
	 * @return CorveeFunctie[]
	 */
	public function getKwalificatiesVanLid($uid) {
		return $this->find('uid = ?', array($uid));
	}

	public function isLidGekwalificeerdVoorFunctie($uid, $fid) {
		return $this->existsByPrimaryKey(array($uid, $fid));
	}

	public function newKwalificatie(CorveeFunctie $functie) {
		$kwalificatie = new CorveeKwalificatie();
		$kwalificatie->functie_id = $functie->functie_id;
		$kwalificatie->wanneer_toegewezen = date('Y-m-d H:i');
		return $kwalificatie;
	}

	public function kwalificatieToewijzen(CorveeKwalificatie $kwali) {
		if ($this->existsByPrimaryKey($kwali->getValues(true))) {
			throw new Exception('Is al gekwalificeerd!');
		}
		$this->create($kwali);
	}

	public function kwalificatieTerugtrekken($uid, $fid) {
		$rowcount = $this->deleteByPrimaryKey(array($uid, $fid));
		if ($rowcount !== 1) {
			throw new Exception('Is niet gekwalificeerd!');
		}
	}

}
