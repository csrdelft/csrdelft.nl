<?php

require_once 'MVC/model/entity/taken/CorveeFunctie.class.php';

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

	public function getAlleFuncties() {
		$functies = $this->find();
		$model = new KwalificatiesModel();
		$kwalificaties = $model->getAlleKwalificaties();
		$functiesByFid = array();
		foreach ($functies as $functie) {
			if ($functie->kwalificatie_benodigd) {
				$functie->kwalificaties = $kwalificaties[$functie->functie_id];
			}
			$functiesByFid[$functie->functie_id] = $functie;
		}
		return $functiesByFid;
	}

	public function getFunctie($fid) {
		$functie = $this->retrieveByPrimaryKey(array($fid));
		$model = new KwalificatiesModel();
		$model->loadKwalificatiesVoorFunctie($functie);
		return $functie;
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
