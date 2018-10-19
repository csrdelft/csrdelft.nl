<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\common\MijnSqli;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\model\entity\bibliotheek\BoekExemplaar;
use CsrDelft\model\entity\bibliotheek\BoekRecensie;

/**
 * RecensieModel.class.php  |  Gerrit Uitslag
 *
 * een boekbeschrijving of boekrecensie
 *
 */
class BoekExemplaarModel extends PersistenceModel {


	const ORM = BoekExemplaar::class;

	public static function getExemplaren(Boek $boek) {
		return self::instance()->find("boek_id = ?", [$boek->id]);
	}
}
