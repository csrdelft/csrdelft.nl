<?php

namespace CsrDelft\model\fiscaat\pin;
use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 */
class PinTransactieMatchModel extends PersistenceModel {
	const ORM = PinTransactieMatch::class;
	/**
	 * @var PinTransactieModel
	 */
	private $pinTransactieModel;
	/**
	 * @var CiviBestellingModel
	 */
	private $civiBestellingModel;

	/**
	 * PinTransactieMatchModel constructor.
	 * @param PinTransactieModel $pinTransactieModel
	 * @param CiviBestellingModel $civiBestellingModel
	 */
	public function __construct(PinTransactieModel $pinTransactieModel, CiviBestellingModel $civiBestellingModel) {
		parent::__construct();
		$this->pinTransactieModel = $pinTransactieModel;
		$this->civiBestellingModel = $civiBestellingModel;
	}


	/**
	 * @param PinTransactieMatch $pinTransactieMatch
	 * @throws CsrException
	 */
	public function getMoment($pinTransactieMatch) {
		if ($pinTransactieMatch->transactie_id !== null) {
			return $this->pinTransactieModel->get($pinTransactieMatch->transactie_id)->datetime;
		} elseif ($pinTransactieMatch->bestelling_id !== null) {
			return $this->civiBestellingModel->get($pinTransactieMatch->bestelling_id)->moment;
		} else {
			throw new CsrException('Pin Transactie Match heeft geen bestelling en transactie.');
		}
	}
}
