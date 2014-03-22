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

	public function __construct() {
		parent::__construct(new CorveeFunctie());
	}

	/**
	 * Eager loading of kwalificaties.
	 * 
	 * @return CorveeFunctie[]
	 */
	public function getAlleFuncties() {
		$model = new KwalificatiesModel();
		$kwalificaties = $model->getAlleKwalificaties();
		$functiesByFid = array();
		$functies = $this->find();
		foreach ($functies as $functie) {
			if (array_key_exists($functie->functie_id, $kwalificaties)) {
				$functie->setKwalificaties($kwalificaties[$functie->functie_id]);
			} else {
				$functie->setKwalificaties(array());
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

	public function removeFunctie($fid) {
		if (TakenModel::existFunctieTaken($fid)) {
			throw new Exception('Verwijder eerst de bijbehorende corveetaken!');
		}
		if (CorveeRepetitiesModel::existFunctieRepetities($fid)) {
			throw new Exception('Verwijder eerst de bijbehorende corveerepetities!');
		}
		if (KwalificatiesModel::existFunctieKwalificaties($fid)) {
			throw new Exception('Verwijder eerst de bijbehorende kwalificaties!');
		}
		return $this->deleteByPrimaryKey(array($fid));
	}

}
