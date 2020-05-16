<?php

namespace CsrDelft\service\fiscaat;

use CsrDelft\model\fiscaat\CiviBestellingModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\security\LoginModel;
use DateTime;

class SaldoGrafiekService {
	/**
	 * @var CiviSaldoModel
	 */
	private $civiSaldoModel;
	/**
	 * @var CiviBestellingModel
	 */
	private $civiBestellingModel;

	public function __construct(CiviSaldoModel $civiSaldoModel, CiviBestellingModel $civiBestellingModel) {
		$this->civiSaldoModel = $civiSaldoModel;
		$this->civiBestellingModel = $civiBestellingModel;
	}

	/**
	 * @param string $uid
	 * @param int $timespan
	 * @return array|null
	 */
	public function getDataPoints($uid, $timespan) {
		if (!$this->magGrafiekZien($uid)) {
			return null;
		}
		$klant = $this->civiSaldoModel->find('uid = ?', array($uid), null, null, 1)->fetch();
		if (!$klant) {
			return null;
		}
		$saldo = $klant->saldo;
		// Teken het huidige saldo
		$data = [['t' => date(DateTime::RFC2822), 'y' => $saldo]];
		$bestellingen = $this->civiBestellingModel->find(
			'uid = ? AND deleted = FALSE AND moment>(NOW() - INTERVAL ? DAY)',
			[$klant->uid, $timespan],
			null,
			'moment DESC'
		);

		foreach ($bestellingen as $bestelling) {
			$data[] = ['t' => date(DateTime::RFC2822, strtotime($bestelling->moment)), 'y' => $saldo];
			$saldo += $bestelling->totaal;
		}

		if (!empty($data)) {
			$row = end($data);
			$time = date(DateTime::RFC2822, strtotime($timespan - 1 . ' days 23 hours ago'));
			array_push($data, ["t" => $time, 'y' => $row['y']]);
		}

		return [
			"labels" => [$time, date(DateTime::RFC2822)],
			"datasets" => [
				[
					'label' => 'Civisaldo',
					'steppedLine' => true,
					'borderWidth' => 2,
					'pointRadius' => 0,
					'hitRadius' => 2,
					'fill' => false,
					'borderColor' => 'green',
					'data' => array_reverse($data),
				],
			],
		];
	}

	/**
	 * @param string $uid
	 * @return bool
	 */
	public function magGrafiekZien($uid) {
		//mogen we uberhaupt een grafiek zien?
		return LoginModel::getUid() === $uid OR LoginModel::mag(P_LEDEN_MOD . ',commissie:SocCie,commissie:MaalCie');
	}
}
