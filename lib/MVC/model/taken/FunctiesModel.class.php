<?php

require_once 'MVC/model/entity/taken/CorveeFunctie.class.php';
require_once 'MVC/model/taken/KwalificatiesModel.class.php';

/**
 * FunctiesModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FunctiesModel extends PersistenceModel {

	protected static $instance;

	protected function __construct() {
		parent::__construct(new CorveeFunctie());
	}

	/**
	 * Optional eager loading of kwalificaties.
	 * 
	 * @param boolean $load_kwalifications
	 * @return CorveeFunctie[]
	 */
	public function getAlleFuncties($load_kwalificaties = false) {
		$functies = $this->find();
		if ($load_kwalificaties) {
			$kwalificaties = KwalificatiesModel::instance()->getAlleKwalificaties();
		}
		$functiesByFid = array();
		foreach ($functies as $functie) {
			if ($load_kwalificaties) {
				if (array_key_exists($functie->functie_id, $kwalificaties)) {
					$functie->setKwalificaties($kwalificaties[$functie->functie_id]);
				} else {
					$functie->setKwalificaties(array());
				}
			}
			$functiesByFid[$functie->functie_id] = $functie;
		}
		return $functiesByFid;
	}

	/**
	 * Lazy loading of kwalificaties.
	 * 
	 * @param int $fid
	 * @return CorveeFunctie[]
	 */
	public function getFunctie($fid) {
		return $this->retrieveByPrimaryKey(array($fid));
	}

	public function newFunctie() {
		$functie = new CorveeFunctie();
		$functie->kwalificatie_benodigd = (bool) Instellingen::get('corvee', 'standaard_kwalificatie');
		return $functie;
	}

	public function removeFunctie(CorveeFunctie $functie) {
		if (TakenModel::existFunctieTaken($functie->functie_id)) {
			throw new Exception('Verwijder eerst de bijbehorende corveetaken!');
		}
		if (CorveeRepetitiesModel::existFunctieRepetities($functie->functie_id)) {
			throw new Exception('Verwijder eerst de bijbehorende corveerepetities!');
		}
		if ($functie->hasKwalificaties()) {
			throw new Exception('Verwijder eerst de bijbehorende kwalificaties!');
		}
		return $this->delete($functie);
	}

}
