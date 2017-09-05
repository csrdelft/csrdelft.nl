<?php

namespace CsrDelft\model\documenten;

use CsrDelft\model\entity\documenten\Document;
use CsrDelft\model\entity\documenten\DocumentCategorie;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class DocumentCategorieModel.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentCategorieModel extends PersistenceModel {
	const ORM = DocumentCategorie::class;

	/**
	 * @param $id
	 *
	 * @return DocumentCategorie|false
	 */
	public function get($id) {
		return $this->retrieveByPrimaryKey([$id]);
	}

	/**
	 * @return array
	 */
	public function getCategorieNamen() {
		$categorien = $this->find();

		$return = [];

		foreach ($categorien as $categorie) {
			$return[$categorie->id] = $categorie->naam;
		}

		return $return;
	}

	/**
	 * @param DocumentCategorie $categorie
	 * @param $aantal
	 *
	 * @return \PDOStatement|Document[]
	 */
	public function getRecent(DocumentCategorie $categorie, $aantal) {
		return DocumentModel::instance()->find(
			'categorie_id = ?',
			[$categorie->id],
			null,
			'toegevoegd DESC',
			$aantal
		);
	}
}
