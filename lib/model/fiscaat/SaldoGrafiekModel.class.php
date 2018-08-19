<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\Saldo;
use CsrDelft\model\security\LoginModel;

class SaldoGrafiekModel {
	const ORM = Saldo::class;

	/**
	 * @param string $uid
	 * @param int $timespan
	 * @return array|null
	 */
	public static function getDataPoints($uid, $timespan) {
		if (!static::magGrafiekZien($uid)) {
			return null;
		}
		$model = CiviSaldoModel::instance();
		$klant = $model->find('uid = ?', array($uid), null, null, 1)->fetch();
		if (!$klant) {
			return null;
		}
		$saldo = $klant->saldo;
		// Teken het huidige saldo
		$data = [[static::flotTime(getDateTime()), round($saldo / 100, 2)]];
		$model = CiviBestellingModel::instance();
		$bestellingen = $model->find(
			'uid = ? AND deleted = FALSE AND moment>(NOW() - INTERVAL ? DAY)',
			[$klant->uid, $timespan],
			null,
			'moment DESC'
		);

		foreach ($bestellingen as $bestelling) {
			$data[] = array(static::flotTime($bestelling->moment), round($saldo / 100, 2));
			$saldo += $bestelling->totaal;
		}

		if (!empty($data)) {
			$row = end($data);
			$time = static::flotTime($timespan - 1 . ' days 23 hours ago');
			array_push($data, [$time, $row[1]]);
		}
		return [
			[
				"label" => "CiviSaldo",
				"data" => array_reverse($data), // Keer de lijst om, flot laat anders veranderingen in de data 1-off zien
				"color" => "green",
				"threshold" => ["below" => 0, "color" => "red"],
				"lines" => ["steps" => true]
			]
		];
	}

	/**
	 * @param string $uid
	 * @return bool
	 */
	public static function magGrafiekZien($uid) {
		//mogen we uberhaupt een grafiek zien?
		return LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,commissie:SocCie,commissie:MaalCie');
	}

	/**
	 * Flot wil graag een timestamp in milliseconden, php kent timestamps in seconden
	 *
	 * @param $moment
	 *
	 * @return false|int
	 */
	private static function flotTime($moment) {
		return strtotime($moment) * 1000;
	}
}
