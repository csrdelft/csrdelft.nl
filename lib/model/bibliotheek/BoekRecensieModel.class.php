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
}
