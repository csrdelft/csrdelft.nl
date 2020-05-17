<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\repository\fiscaat\CiviProductRepository;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviBestellingInhoudModel extends PersistenceModel {
	/**
	 * ORM class.
	 */
	const ORM = CiviBestellingInhoud::class;

	/**
	 * @var CiviProductRepository
	 */
	private $civiProductRepository;

	public function __construct(CiviProductRepository $civiProductRepository) {
		parent::__construct();

		$this->civiProductRepository = $civiProductRepository;
	}

	/**
	 * @param CiviBestellingInhoud $inhoud
	 *
	 * @return int
	 */
	public function getPrijs(CiviBestellingInhoud $inhoud) {
		$product = $this->civiProductRepository->getProduct($inhoud->product_id);

		return $product->tmpPrijs * $inhoud->aantal;
	}

	/**
	 * @param CiviBestellingInhoud $inhoud
	 *
	 * @return string
	 */
	public function getBeschrijving(CiviBestellingInhoud $inhoud) {
		$product = $this->civiProductRepository->getProduct($inhoud->product_id);
		return sprintf("%d %s", $inhoud->aantal, $product->beschrijving);
	}

	/**
	 * PK van CiviBestellingInhoud is [bestelling_id, product_id] en een combi van de twee is dus uniek.
	 *
	 * @param int $bestelling_id
	 * @param int $product_id
	 * @return CiviBestellingInhoud|false
	 */
	public function getVoorBestellingEnProduct($bestelling_id, $product_id) {
		return $this->find('bestelling_id = ? AND product_id = ?', [$bestelling_id, $product_id])->fetch();
	}
}
