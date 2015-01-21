<?php

require_once 'model/maalcie/KwalificatiesModel.class.php';

/**
 * FunctiesModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FunctiesModel extends CachedPersistenceModel {

	const orm = 'CorveeFunctie';

	protected static $instance;

	/**
	 * Lazy loading of kwalificaties.
	 * 
	 * @param int $fid
	 * @return CorveeFunctie[]
	 */
	public static function get($fid) {
		return static::instance()->retrieveByPrimaryKey(array($fid));
	}

	protected function __construct() {
		parent::__construct('maalcie/');
	}

	/**
	 * Optional eager loading of kwalificaties.
	 * 
	 * @param boolean $load_kwalificaties
	 * @return CorveeFunctie[]
	 */
	public function getAlleFuncties() {
		return group_by_distinct('functie_id', $this->prefetch());
	}

	public function nieuw() {
		$functie = new CorveeFunctie();
		$functie->kwalificatie_benodigd = (boolean) Instellingen::get('corvee', 'standaard_kwalificatie');
		return $functie;
	}

	public function removeFunctie(CorveeFunctie $functie) {
		if (CorveeTakenModel::existFunctieTaken($functie->functie_id)) {
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
