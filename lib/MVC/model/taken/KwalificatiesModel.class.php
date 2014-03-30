<?php

/**
 * KwalificatiesModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class KwalificatiesModel extends PersistenceModel {

	protected static $instance;
	protected static $orm = 'CorveeKwalificatie';

	/**
	 * Lazy loading of corveefunctie.
	 * 
	 * @return CorveeKwalificatie[]
	 */
	public function getAlleKwalificaties() {
		return array_group_by('functie_id', $this->find());
	}

	public function getKwalificatiesVoorFunctie($fid) {
		return $this->find('functie_id = ?', array($fid));
	}

	/**
	 * Eager loading of corveefuncties.
	 * 
	 * @param string $lid_id
	 * @return CorveeFunctie[]
	 */
	public function getKwalificatiesVanLid($lid_id) {
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$kwalificaties = $this->find('lid_id = ?', array($lid_id));
		foreach ($kwalificaties as $kwali) {
			$kwali->setCorveeFunctie($functies[$kwali->functie_id]);
		}
		return $kwalificaties;
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
		if (!$this->deleteByPrimaryKey(array($uid, $fid))) {
			throw new Exception('Is niet gekwalificeerd!');
		}
	}

}
