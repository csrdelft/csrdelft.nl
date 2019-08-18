<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\Orm\PersistenceModel;
use CsrDelft\model\entity\bibliotheek\BoekRecensie;

/**
 * RecensieModel.class.php  |  Gerrit Uitslag
 *
 * een boekbeschrijving of boekrecensie
 *
 */
class BoekRecensieModel extends PersistenceModel {


	const ORM = BoekRecensie::class;
	/**
	 * @param $id
	 * @return BoekRecensie[]
	 */
	public function getVoorBoek($id) {
		return $this->find("boek_id = ?", [$id])->fetchAll();
	}

	public function get(int $boek_id, string $uid) : BoekRecensie {
		$recensie = $this->find("boek_id= ? and schrijver_uid = ?", [$boek_id, $uid])->fetch();
		if ($recensie === false) {
			$recensie = new BoekRecensie();
			$recensie->boek_id = $boek_id;
			$recensie->schrijver_uid = $uid;
			$recensie->toegevoegd = getDateTime();
		}
		return $recensie;
	}

	/**
	 * @param $uid
	 * @return BoekRecensie[]
	 */
	public function getVoorLid($uid) {
		return $this->find("schrijver_uid = ?", [$uid])->fetchAll();
	}
}
