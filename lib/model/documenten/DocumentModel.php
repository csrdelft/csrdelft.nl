<?php

namespace CsrDelft\model\documenten;

use CsrDelft\model\entity\documenten\Document;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class DocumentModel.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class DocumentModel extends PersistenceModel {
	const ORM = Document::class;

	protected function __construct() {
		parent::__construct();
	}

	/**
	 * @return DocumentCategorieModel
	 */
	public function getCategorieModel() {
		return DocumentCategorieModel::instance();
	}

	/**
	 * @param $id
	 *
	 * @return Document|false
	 */
	public function get($id) {
		return $this->retrieveByPrimaryKey([$id]);
	}

	/**
	 * @param $zoekterm
	 * @param int $limiet
	 *
	 * @return \PDOStatement|Document[]
	 */
	public function zoek($zoekterm, $limiet = 0) {

		return $this->find(
			'MATCH (naam, filename) AGAINST (? IN NATURAL LANGUAGE MODE)',
			[$zoekterm],
			null,
			null,
			$limiet
		);

	}
}
