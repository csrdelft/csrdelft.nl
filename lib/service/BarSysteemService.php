<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\fiscaat\CiviSaldo;
use CsrDelft\repository\fiscaat\CiviBestellingRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class BarSysteemService
{
	/**
	 * @var Connection|PDO
	 */
	private $db;

	public function getProfiel($uid)
	{
		$q = $this->db->prepare(
			<<<SQL
SELECT profielen.voornaam, profielen.voorletters, profielen.tussenvoegsel, profielen.achternaam, profielen.status, profielen.email, accounts.email as accountEmail
FROM profielen LEFT JOIN accounts ON accounts.uid = profielen.uid
WHERE profielen.uid = :uid
SQL
		);
		$q->bindValue('uid', $uid);

		$q->execute();

		return $q->fetch(PDO::FETCH_ASSOC);
	}

	public function getNaam($profiel): string
	{
		if (empty($profiel['voornaam'])) {
			$naam = $profiel['voorletters'] . ' ';
		} else {
			$naam = $profiel['voornaam'] . ' ';
		}
		if (!empty($profiel['tussenvoegsel'])) {
			$naam .= $profiel['tussenvoegsel'] . ' ';
		}
		$naam .= $profiel['achternaam'];

		return $naam;
	}

	public function getGrootboeken()
	{
		$q = $this->db->prepare(
			"SELECT id, type FROM civi_categorie WHERE cie='soccie'"
		);
		$q->execute();
		return $q->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * @param null|scalar $socCieId
	 */
	public function getSaldo($socCieId)
	{
		$q = $this->db->prepare(
			'SELECT saldo FROM civi_saldo WHERE uid = :socCieId'
		);
		$q->bindValue(':socCieId', $socCieId);
		$q->execute();
		return $q->fetchColumn();
	}

	// Beheer

	/**
	 * @return (array[]|string)[][]
	 *
	 * @psalm-return array<array{content: list{array{type: mixed, total: mixed},...}, title?: string}>
	 */
	public function getGrootboekInvoer(): array
	{
		// GROUP BY week
		$q = $this->db->prepare("
SELECT G.type,
	SUM(I.aantal * PR.prijs) AS total,
	WEEK(B.moment, 3) AS week,
    YEAR(B.moment) as year,
	YEARWEEK(B.moment, 3) AS yearweek
FROM civi_bestelling AS B
JOIN civi_bestelling_inhoud AS I ON
	B.id = I.bestelling_id
JOIN civi_product AS P ON
	I.product_id = P.id
JOIN civi_prijs AS PR ON
	P.id = PR.product_id
	AND (B.moment > PR.van AND (B.moment < PR.tot OR PR.tot IS NULL))
JOIN civi_categorie AS G ON
	P.categorie_id = G.id
WHERE
	B.deleted = 0 AND
	G.status = 1 AND
	B.cie != 'maalcie'
GROUP BY
	yearweek,
	G.id
ORDER BY yearweek DESC
		");
		$q->execute();

		$weeks = [];

		while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
			$exists = isset($weeks[$r['yearweek']]);

			$week = $exists ? $weeks[$r['yearweek']] : [];

			if ($exists) {
				$week['content'][] = ['type' => $r['type'], 'total' => $r['total']];
			} else {
				$week['content'] = [['type' => $r['type'], 'total' => $r['total']]];
				$week['title'] = 'Week ' . $r['week'] . ', ' . $r['year'];
			}

			$weeks[$r['yearweek']] = $week;
		}

		return $weeks;
	}

	/**
	 * @psalm-return array{sum_saldi: mixed, sum_saldi_lid: mixed, red: mixed}
	 */
	public function getToolData(): array
	{
		$data = [];

		$data['sum_saldi'] = $this->sumSaldi();
		$data['sum_saldi_lid'] = $this->sumSaldi(true);
		$data['red'] = $this->getRed();

		return $data;
	}

	private function sumSaldi(bool $profielOnly = false)
	{
		$after = $profielOnly ? "AND uid NOT LIKE 'c%'" : '';

		return $this->db
			->query(
				'SELECT SUM(saldo) AS sum FROM civi_saldo WHERE deleted = 0 ' . $after
			)
			->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * @return array[]
	 *
	 * @psalm-return list{0?: array{naam: mixed, email: mixed, saldo: mixed, status: mixed},...}
	 */
	private function getRed(): array
	{
		$result = [];

		$q = $this->db->query(
			"SELECT uid, saldo FROM civi_saldo WHERE deleted = 0 AND saldo < 0 AND uid NOT LIKE 'c%' ORDER BY saldo"
		);
		while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
			$profiel = $this->getProfiel($r['uid']);

			$result[] = [
				'naam' => $this->getNaam($profiel),
				'email' => $profiel['accountEmail'] ?? $profiel['email'], // ?: "rood" ?? $profiel['accountEmail'],
				'saldo' => $r['saldo'],
				'status' => $profiel['status'],
			];
		}

		return $result;
	}

	/**
	 * @param null|scalar $name
	 * @param null|scalar $price
	 * @param null|scalar $type
	 */
	public function addProduct($name, $price, $type): bool
	{
		if ($type < 1) {
			return false;
		}

		$this->db->beginTransaction();

		$q = $this->db->prepare(
			'INSERT INTO civi_product(status, beschrijving, prioriteit, categorie_id, beheer) VALUES(1, :name, -5000, :type, 0)'
		);
		$q->bindValue(':name', $name);
		$q->bindValue(':type', $type);
		$q->execute();

		$q = $this->db->prepare(
			'INSERT INTO civi_prijs(product_id, prijs) VALUES(:productId, :price)'
		);
		$q->bindValue(':productId', $this->db->lastInsertId());
		$q->bindValue(':price', $price);
		$q->execute();

		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}

		return true;
	}

	/**
	 * @param null|scalar $id
	 */
	public function removePerson($id)
	{
		$q = $this->db->prepare(
			'UPDATE civi_saldo SET deleted = 1 WHERE uid = :id AND saldo = 0'
		);
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		$q->execute();
		return $q->rowCount();
	}

	/**
	 * @param null|scalar $name
	 * @param null|scalar $saldo
	 * @param null|scalar $uid
	 */
	public function addPerson($name, $saldo, $uid): bool|\Doctrine\DBAL\Result
	{
		$q = $this->db->prepare(
			'INSERT INTO civi_saldo (naam, saldo, uid) VALUES (:naam, :saldo, :uid)'
		);
		$q->bindValue(':naam', $name, PDO::PARAM_STR);
		$q->bindValue(':saldo', $saldo, PDO::PARAM_STR);
		if (!empty($uid)) {
			$q->bindValue(':uid', $uid, PDO::PARAM_STR);
		} else {
			$latest = $this->db
				->query(
					"SELECT uid FROM civi_saldo WHERE uid LIKE 'c%' ORDER BY uid DESC LIMIT 1"
				)
				->fetchColumn();
			$q->bindValue(':uid', ++$latest, PDO::PARAM_STR);
		}

		return $q->execute();
	}

	/**
	 * @param null|scalar $productId
	 * @param null|scalar $price
	 */
	public function updatePrice($productId, $price): bool
	{
		$this->db->beginTransaction();

		$q = $this->db->prepare(
			'UPDATE civi_prijs SET tot = CURRENT_TIMESTAMP WHERE product_id = :productId AND tot IS NULL ORDER BY van DESC LIMIT 1'
		);
		$q->bindValue(':productId', $productId);
		$q->execute();

		$q = $this->db->prepare(
			'INSERT INTO civi_prijs (product_id, prijs) VALUES (:productId, :prijs)'
		);
		$q->bindValue(':productId', $productId);
		$q->bindValue(':prijs', $price);
		$q->execute();

		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}

		return true;
	}

	/**
	 * @param null|scalar $productId
	 * @param null|scalar $visibility
	 */
	public function updateVisibility($productId, $visibility): bool
	{
		$this->db->beginTransaction();

		$q = $this->db->prepare(
			'UPDATE civi_product SET status = :visibility WHERE id = :productId'
		);
		$q->bindValue(':productId', $productId);
		$q->bindValue(':visibility', $visibility);
		$q->execute();

		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}

		return true;
	}

	// Log action by type

}
