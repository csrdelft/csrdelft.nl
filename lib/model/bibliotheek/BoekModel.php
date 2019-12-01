<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

/**
 * BiebBoek.php  |  Gerrit Uitslag
 *
 * boeken
 *
 */
class BoekModel extends PersistenceModel {

	const ORM = Boek::class;

	public function existsTitel($value) {
		return self::instance()->find('titel = ?', [$value])->rowCount() > 0;
	}

	/**
	 * @param string $zoekveld
	 * @param string $zoekterm
	 * @return Boek[]
	 * @throws CsrGebruikerException
	 */
	public function autocompleteProperty(string $zoekveld, string $zoekterm) {
		$allowedFields = ['titel', 'auteur', 'taal'];
		if (!in_array($zoekveld, $allowedFields)) {
			throw new CsrGebruikerException("Autocomplete niet toegestaan voor dit veld");
		}
		return $this->find("$zoekveld like CONCAT('%', ?, '%')", [$zoekterm]);
	}

	/**
	 * @param string $zoekterm
	 * @return Boek[]
	 * @throws CsrGebruikerException
	 */
	public function autocompleteBoek(string $zoekterm) {
		return $this->find("titel like CONCAT('%',?,'%')", [$zoekterm])->fetchAll();
	}

	/**
	 * @param $id
	 * @return PersistentEntity|false|Boek
	 */
	public function get($id) {
		return $this->retrieveByPrimaryKey([$id]);
	}
}
