<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\entity\fiscaat\CiviProductTypeEnum;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;
use DateTime;
use Exception;
use Generator;
use PDO;
use PDOStatement;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviBestellingModel extends PersistenceModel {
	/**
	 * ORM class.
	 */
	const ORM = CiviBestelling::class;

	/**
	 * @var CiviBestellingInhoudModel
	 */
	private $civiBestellingInhoudModel;

	/**
	 * @var CiviProductModel
	 */
	private $civiProductModel;
	/**
	 * @var CiviSaldoModel
	 */
	private $civiSaldoModel;

	/**
	 * CiviBestellingModel constructor.
	 * @param CiviBestellingInhoudModel $civiBestellingInhoudModel
	 * @param CiviProductModel $civiProductModel
	 * @param CiviSaldoModel $civiSaldoModel
	 */
	public function __construct(
		CiviBestellingInhoudModel $civiBestellingInhoudModel,
		CiviProductModel $civiProductModel,
		CiviSaldoModel $civiSaldoModel
	) {
		parent::__construct();

		$this->civiBestellingInhoudModel = $civiBestellingInhoudModel;
		$this->civiProductModel = $civiProductModel;
		$this->civiSaldoModel = $civiSaldoModel;
	}

	/**
	 * @param int $id
	 * @return CiviBestelling
	 */
	public function get($id) {
		return $this->find('id = ?', [$id])->fetch();
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @return CiviBestellingInhoud[]
	 */
	public function getPinBestellingInMoment($from, $to) {
		/** @var CiviBestelling[] $bestellingen */
		$bestellingen = $this->find('moment > ? AND moment < ? AND deleted = false', [$from, $to], null, 'moment ASC');
		$pinBestellingen = [];

		foreach ($bestellingen as $bestelling) {
			$bestellingInhoud = $bestelling->getInhoud();

			foreach ($bestellingInhoud as $item) {
				if ($item->product_id == CiviProductTypeEnum::PINTRANSACTIE) {
					$pinBestellingen[] = $item;
				}
			}
		}

		return $pinBestellingen;
	}

	/**
	 * @param CiviBestelling $bestelling
	 */
	public function revert(CiviBestelling $bestelling) {
		return $this->database->_transaction(function () use ($bestelling) {
			/**
			 * @var CiviBestelling|false $bestelling
			 */
			$bestelling = $this->retrieve($bestelling);
			if ($bestelling === false || $bestelling->deleted) {
				throw new Exception("Bestelling bestaat niet, kan niet worden teruggedraaid.");
			}
			$this->civiSaldoModel->ophogen($bestelling->uid, $bestelling->totaal);
			$bestelling->deleted = true;
			$this->civiSaldoModel->update($bestelling);

		});
	}

	/**
	 * @param string $uid
	 * @param int $limit
	 *
	 * @return PDOStatement
	 */
	public function getAlleBestellingenVoorLid($uid, $limit = null) {
		return $this->find('uid = ?', [$uid], null, 'moment DESC', $limit);
	}

	/**
	 * @param string $uid
	 * @param int $limit
	 *
	 * @return PDOStatement
	 */
	public function getBestellingenVoorLid($uid, $limit = null) {
		return $this->find('uid = ? AND deleted = FALSE', array($uid), null, 'moment DESC', $limit);
	}

	/**
	 * @param DateTime $date
	 * @param bool $profielOnly
	 *
	 * @return mixed
	 */
	public function getSomBestellingenVanaf(DateTime $date, $profielOnly = false) {
		$after = $profielOnly ? "AND uid NOT LIKE 'c%'" : "";
		$moment = $date->format("Y-m-d G:i:s");
		return $this->select(['SUM(totaal)'], "deleted = 0 AND moment > ? $after", [$moment])->fetch(PDO::FETCH_COLUMN);
	}

	/**
	 * @param CiviBestelling $bestelling
	 * @return string
	 */
	public function getPinBeschrijving($bestelling) {
		/** @var CiviBestellingInhoud $inhoud */
		$inhoud = $this->civiBestellingInhoudModel->getVoorBestellingEnProduct($bestelling->id, CiviProductTypeEnum::PINTRANSACTIE);

		if ($inhoud === false) {
			return "";
		}

		$beschrijving = sprintf('€%.2f PIN', $inhoud->aantal / 100);

		$aantalInhoud = $this->civiBestellingInhoudModel->count('bestelling_id = ?', [$bestelling->id]);

		if ($aantalInhoud == 2) {
			$beschrijving .= sprintf(' en 1 ander product');
		} elseif ($aantalInhoud > 2) {
			$beschrijving .= sprintf(' en %d andere producten', $aantalInhoud - 1);
		}

		return $beschrijving;
	}

	/**
	 * @param CiviBestelling[] $bestellingen
	 * @return Generator|object[]
	 */
	public function getBeschrijving($bestellingen) {
		foreach ($bestellingen as $bestelling) {
			/** @var CiviBestellingInhoud[] $inhoud */
			$inhoud = $this->civiBestellingInhoudModel->find('bestelling_id = ?', array($bestelling->id));
			$bestellingInhoud = [];
			foreach ($inhoud as $item) {
				$bestellingInhoud[] = $this->civiBestellingInhoudModel->getBeschrijving($item);
			}

			yield (object)[
				'inhoud' => $bestellingInhoud,
				'moment' => $bestelling->moment,
				'totaal' => $bestelling->totaal
			];
		}
	}

	/**
	 * @param CiviBestellingInhoud[] $bestellingen
	 *
	 * @return string
	 */
	public function getBeschrijvingText($bestellingen) {
		$bestellingenInhoud = [];
		foreach ($bestellingen as $item) {
			$bestellingenInhoud[] = $this->civiBestellingInhoudModel->getBeschrijving($item);
		}
		return implode(", ", $bestellingenInhoud);
	}

	public function vanBedragInCenten($bedrag, $uid) {
		$bestelling = new CiviBestelling();
		$bestelling->cie = 'anders';
		$bestelling->uid = $uid;
		$bestelling->deleted = false;
		$bestelling->moment = getDateTime();

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = -$bedrag;
		$inhoud->product_id = CiviProductTypeEnum::OVERGEMAAKT;

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal = $this->civiProductModel->getProduct($inhoud->product_id)->prijs * -$bedrag;

		return $bestelling;
	}

	/**
	 * @param PersistentEntity|CiviBestelling $entity
	 * @return string
	 */
	public function create(PersistentEntity $entity) {
		$entity->id = parent::create($entity);

		foreach ($entity->inhoud as $bestelling) {
			$bestelling->bestelling_id = $entity->id;
			$this->civiBestellingInhoudModel->create($bestelling);
		}

		return $entity->id;
	}
}
