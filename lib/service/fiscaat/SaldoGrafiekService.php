<?php

namespace CsrDelft\service\fiscaat;

use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use DateInterval;
use DateTime;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class SaldoGrafiekService
{
	public function __construct(
		private readonly Security $security,
		private readonly CiviSaldoRepository $civiSaldoRepository,
		private readonly CiviBestellingRepository $civiBestellingRepository
	) {
	}

	/**
	 * @param string $uid
	 * @param int $timespan
	 * @return array|null
	 * @throws Exception
	 */
	public function getDataPoints($uid, $timespan)
	{
		if (!$this->magGrafiekZien($uid)) {
			return null;
		}
		$klant = $this->civiSaldoRepository->getSaldo($uid);
		if (!$klant) {
			return null;
		}
		$saldo = $klant->saldo;
		// Teken het huidige saldo
		$data = [['t' => date(DateTime::RFC2822), 'y' => $saldo]];
		$bestellingen = $this->civiBestellingRepository
			->createQueryBuilder('b')
			->where('b.uid = :uid and b.deleted = false and b.moment > :moment')
			->setParameter('uid', $klant->uid)
			->setParameter(
				'moment',
				date_create_immutable()->sub(new DateInterval('P' . $timespan . 'D'))
			)
			->orderBy('b.moment', 'DESC')
			->getQuery()
			->getResult();

		foreach ($bestellingen as $bestelling) {
			$data[] = [
				't' => $bestelling->moment->format(DateTime::RFC2822),
				'y' => $saldo,
			];
			$saldo += $bestelling->totaal;
		}

		$row = end($data);
		$time = date(
			DateTime::RFC2822,
			strtotime($timespan - 1 . ' days 23 hours ago')
		);
		array_push($data, ['t' => $time, 'y' => $row['y']]);

		return [
			'labels' => [$time, date(DateTime::RFC2822)],
			'datasets' => [
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
	public function magGrafiekZien($uid)
	{
		//mogen we uberhaupt een grafiek zien?
		return $this->security->isGranted(
			$uid . ',ROLE_LEDEN_MOD,commissie:SocCie,commissie:MaalCie'
		);
	}
}
