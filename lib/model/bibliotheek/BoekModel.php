<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\MijnSqli;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
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

	/**
	 * @param string $zoekveld
	 * @param string $zoekterm
	 * @return String[]
	 * @throws CsrGebruikerException
	 */
	public static function autocompleteProperty(string $zoekveld, string $zoekterm) {
		$allowedFields = ['titel', 'auteur', 'taal'];
		if (!in_array($zoekveld, $allowedFields)) {
			throw new CsrGebruikerException("Autocomplete niet toegestaan voor dit veld");
		}
		$queryResults = Database::instance()->sqlSelect([$zoekveld], "biebboek", "$zoekveld like CONCAT('%',?,'%')", [$zoekterm], $zoekveld);
		$results = [];
		foreach ($queryResults as $queryResult) {
			$results[] = $queryResult[$zoekveld];
		}
		return $results;
	}

	/**
	 * @param string $zoekterm
	 * @return Boek[]
	 * @throws CsrGebruikerException
	 */
	public static function autocompleteBoek(string $zoekterm) {
		return self::instance()->find("titel like CONCAT('%',?,'%')", [$zoekterm])->fetchAll();
	}

	public function get($id) {
		return self::instance()->retrieveByPrimaryKey([$id]);
	}
}
