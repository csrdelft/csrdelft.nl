<?php

namespace CsrDelft\model;

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\SavedQuery;
use CsrDelft\model\entity\SavedQueryResult;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

class SavedQueryModel extends PersistenceModel {
	const ORM = SavedQuery::class;

	public function get($id) {
		return $this->retrieveByPrimaryKey([$id]);
	}

	/**
	 * @return SavedQuery[]|\PDOStatement
	 */
	static public function getQueries() {
		return static::instance()->find();
	}

	public function loadQuery($queryId) {
		/** @var SavedQuery $query */
		$query = $this->retrieveByPrimaryKey([$queryId]);

		if (!$query || !$query->magBekijken()) {
			throw new CsrToegangException();
		}

		$resultObject = new SavedQueryResult();
		$resultObject->query = $query;

		try {
			$result = $this->database->getDatabase()->query($query->savedquery);
			$numCols = $result->columnCount();
			$cols = [];
			for ($i = 0; $i < $numCols; $i++) {
				$col = $result->getColumnMeta($i);

				$cols[] = $col['name'];
			}

			$resultObject->cols = $cols;
			$resultObject->rows = $result->fetchAll(\PDO::FETCH_ASSOC);
		} catch (\PDOException $ex) {
			$resultObject->cols = [];
			$resultObject->rows = [];
			$resultObject->error = $ex->getMessage();
		}

		return $resultObject;
	}
}
