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

	public function getAlleFuncties($groupByFid = false) {
		$functies = $this->find();
		if ($groupByFid) {
			$functiesByFid = array();
			foreach ($functies as $functie) {
				$functiesByFid[$functie->functie_id] = $functie;
			}
			return $functiesByFid;
		}
		return $functies;
	}

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
		KwalificatiesModel::verwijderKwalificaties($fid); // delete kwalificaties first (foreign key)
		return $this->deleteByPrimaryKey(array($fid));
	}

}
