<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\common\MijnSqli;
use CsrDelft\model\security\LoginModel;
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
	public static function getVoorBoek($id) {
		return self::instance()->find("boek_id = ?", [$id])->fetchAll();
	}

	public static function get(int $boek_id, string $uid) : BoekRecensie {
		$recensie = self::instance()->find("boek_id= ? and schrijver_uid = ?", [$boek_id, $uid])->fetch();
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
	public static function getVoorLid($uid) {
		return self::instance()->find("schrijver_uid = ?", [$uid])->fetchAll();
	}
}
