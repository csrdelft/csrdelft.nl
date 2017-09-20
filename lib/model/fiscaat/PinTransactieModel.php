<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\PinTransactie;
use CsrDelft\Orm\PersistenceModel;

/**
 * Class PinTransactieModel
 *
 * @package model\fiscaat
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 06/09/2017
 */
class PinTransactieModel extends PersistenceModel {
	const ORM = PinTransactie::class;
	protected static $instance;

	/**
	 * Haal een lijst op met bestellingen waarvoor geen pinbetaling bestaat.
	 *
	 * @param string $from
	 * @param string $to
	 * @return int[]
	 */
	public function getUnmatched($from, $to) {
		$statement = $this->database->getDatabase()->prepare(
			<<<'SQL'
SELECT bestelling_id
FROM CiviBestellingInhoud
INNER JOIN CiviBestelling
ON CiviBestellingInhoud.bestelling_id = CiviBestelling.id
WHERE CiviBestellingInhoud.product_id = 24
AND CiviBestellingInhoud.bestelling_id NOT IN (
	SELECT bestelling_id FROM pin_transactie WHERE bestelling_id IS NOT NULL
)
AND moment > ? AND moment < ?
SQL
		);
		$statement->setFetchMode(\PDO::FETCH_COLUMN, 0);
		$statement->execute([$from, $to]);

		return $statement->fetchAll();
	}

	/**
	 * Probeer een match te vinden voor een pintransactie.
	 *
	 * @param PinTransactie $pinTransactie
	 * @return bool
	 */
	public function match(PinTransactie $pinTransactie) {
		$statement = $this->database->getDatabase()->prepare(
			<<<'SQL'
SELECT bestelling_id, aantal, moment
FROM CiviBestellingInhoud
INNER JOIN CiviBestelling
ON CiviBestellingInhoud.bestelling_id = CiviBestelling.id
WHERE CiviBestellingInhoud.product_id = 24
AND aantal = ?
ORDER BY ABS(TIMEDIFF(moment, ?)) 
LIMIT 1
SQL
);
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		$success = $statement->execute([$pinTransactie->getBedragInCenten(), $pinTransactie->datetime]);

		if ($success === false) {
			return false;
		}

		$result = $statement->fetch();

		if ($result === false) {
			return false;
		}

		// Meer dan vijf minuten tussen betaling en bestelling is gek.
		if (abs(strtotime($result['moment']) - strtotime($pinTransactie->datetime)) > 300) {
			return false;
		}

		if ($this->find('bestelling_id = ?', [$result['bestelling_id']])->fetch() !== false) {
			return false;
		}

		$pinTransactie->bestelling_id = $result['bestelling_id'];

		$this->update($pinTransactie);

		return true;
	}
}
