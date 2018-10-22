<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\MijnSqli;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\InputField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\SelectField;

/**
 * BiebBoek.php  |  Gerrit Uitslag
 *
 * boeken
 *
 */
class BoekModel extends PersistenceModel {

	const ORM = Boek::class;

	public static function existsTitel($value) {
		return self::instance()->find('titel = ?', [$value])->rowCount() > 0;
	}


	public function get($id) {
		return self::instance()->retrieveByPrimaryKey([$id]);
	}
	/**
	 * Voeg exemplaar toe
	 *
	 * @param string $eigenaar uid
	 * @return bool true geslaagd
	 *      false  mislukt
	 *          $eigenaar is ongeldig uid
	 */
	public function addExemplaar($eigenaar) {
		if (!AccountModel::isValidUid($eigenaar)) {
			return false;
		}
		$db = MijnSqli::instance();
		$qSave = "
			INSERT INTO biebexemplaar (
				boek_id, eigenaar_uid, toegevoegd, status
			) VALUES (
				" . (int)$this->getId() . ",
				'" . $db->escape($eigenaar) . "',
				'" . getDateTime() . "',
				'beschikbaar'
			);";
		if ($db->query($qSave)) {
			return true;
		}
		$this->error .= 'Fout in query, mysql gaf terug: ' . $db->error() . ' Boek::addExemplaar()';
		return false;
	}


}
