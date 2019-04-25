<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

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
	 * @return Boek[]
	 * @throws CsrGebruikerException
	 */
	public static function autocompleteProperty(string $zoekveld, string $zoekterm) {
		$allowedFields = ['titel', 'auteur', 'taal'];
		if (!in_array($zoekveld, $allowedFields)) {
			throw new CsrGebruikerException("Autocomplete niet toegestaan voor dit veld");
		}
		return static::instance()->find("$zoekveld like CONCAT('%', ?, '%')", [$zoekterm]);
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
