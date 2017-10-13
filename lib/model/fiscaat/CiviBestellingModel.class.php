<?php

namespace CsrDelft\model\fiscaat;

use function CsrDelft\getDateTime;
use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

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
	 * CiviBestellingModel constructor.
	 * @param CiviBestellingInhoudModel $civiBestellingInhoudModel
	 * @param CiviProductModel $civiProductModel
	 */
	public function __construct(
		CiviBestellingInhoudModel $civiBestellingInhoudModel,
		CiviProductModel $civiProductModel
	) {
		parent::__construct();

		$this->civiBestellingInhoudModel = $civiBestellingInhoudModel;
		$this->civiProductModel = $civiProductModel;
	}

	/**
	 * @param string $uid
	 * @param int $limit
	 *
	 * @return \PDOStatement
	 */
	public function getAlleBestellingenVoorLid($uid, $limit = null) {
		return $this->find('uid = ?', [$uid], null, 'moment DESC', $limit);
	}

	/**
	 * @param string $uid
	 * @param int $limit
	 *
	 * @return \PDOStatement
	 */
	public function getBestellingenVoorLid($uid, $limit = null) {
		return $this->find('uid = ? AND deleted = FALSE', array($uid), null, 'moment DESC', $limit);
	}

	/**
	 * @param CiviBestelling[] $bestellingen
	 * @return \Generator|object[]
	 */
	public function getBeschrijving($bestellingen) {
		foreach ($bestellingen as $bestelling) {
			/** @var CiviBestellingInhoud[] $inhoud */
			$inhoud = $this->civiBestellingInhoudModel->find('bestelling_id = ?', array($bestelling->id));
			$bestellingInhoud = [];
			foreach ($inhoud as $item) {
				$bestellingInhoud[] = $this->civiBestellingInhoudModel->getBeschrijving($item);
			}

			yield (object) [
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
		$inhoud->product_id = 6; // TODO dynamic, is cent

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
