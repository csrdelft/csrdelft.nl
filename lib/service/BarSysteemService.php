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
	/**
	 * @var CiviSaldoRepository
	 */
	private $civiSaldoRepository;
	/**
	 * @var CiviProductRepository
	 */
	private $civiProductRepository;
	/**
	 * @var CiviBestellingRepository
	 */
	private $civiBestellingRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager,
		CiviSaldoRepository $civiSaldoRepository,
		CiviProductRepository $civiProductRepository,
		CiviBestellingRepository $civiBestellingRepository
	) {
		$this->db = DriverManager::getConnection([
			'url' => $_ENV['DATABASE_URL'],
			'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"],
		])->getWrappedConnection();
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->civiProductRepository = $civiProductRepository;
		$this->civiBestellingRepository = $civiBestellingRepository;
		$this->entityManager = $entityManager;
	}

	/**
	 * @return CiviSaldo[]
	 */
	public function getPersonen()
	{
		return $this->civiSaldoRepository->findBy(['deleted' => false]);
	}

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

	public function getNaam($profiel)
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
	 * Maak een nieuwe bestelling aan.
	 * @param string $uid
	 * @param string $cie
	 * @param $inhoud
	 * @return bool|mixed
	 */
	public function verwerkBestelling($uid, $cie, $inhoud)
	{
		$em = $this->entityManager;
		return $em->transactional(function () use ($em, $uid, $cie, $inhoud) {
			$civiBestelling = new CiviBestelling();

			$civiSaldo = $this->civiSaldoRepository->find($uid);

			$civiBestelling->civiSaldo = $civiSaldo;
			$civiBestelling->cie = $cie;
			$civiBestelling->deleted = false;
			$civiBestelling->moment = date_create_immutable();

			$em->persist($civiBestelling);

			$em->flush();

			$totaal = 0;

			foreach ($inhoud as $id => $aantal) {
				$nieuwBestellingInhoud = new CiviBestellingInhoud();
				$nieuwBestellingInhoud->aantal = $aantal;
				$nieuwBestellingInhoud->setProduct(
					$this->civiProductRepository->find($id)
				);
				$nieuwBestellingInhoud->setBestelling($civiBestelling);

				$totaal += $aantal * $nieuwBestellingInhoud->product->getPrijsInt();

				$civiBestelling->inhoud->add($nieuwBestellingInhoud);

				$em->persist($nieuwBestellingInhoud);
			}

			$civiBestelling->totaal = $totaal;

			$civiSaldo->saldo -= $totaal;
			$civiSaldo->laatst_veranderd = date_create_immutable();

			$em->flush();

			return null;
		});
	}

	/**
	 * @return CiviProduct[]
	 */
	public function getProducten()
	{
		return $this->civiProductRepository->findByCie('soccie');
	}

	public function verwerkBestellingVoorCommissie($data, $cie = 'soccie')
	{
		$this->db->beginTransaction();

		$q = $this->db->prepare(
			'INSERT INTO civi_bestelling (uid, cie, totaal) VALUES (:socCieId, :commissie, 0);'
		);
		$q->bindValue(':socCieId', $data->persoon->socCieId, PDO::PARAM_STR);
		$q->bindValue(':commissie', $cie, PDO::PARAM_STR);
		$q->execute();
		$bestelId = $this->db->lastInsertId();
		foreach ($data->bestelLijst as $productId => $aantal) {
			$q = $this->db->prepare(
				'INSERT INTO civi_bestelling_inhoud VALUES (:bestelId,  :productId, :aantal);'
			);
			$q->bindValue(':productId', $productId, PDO::PARAM_INT);
			$q->bindValue(':aantal', $aantal, PDO::PARAM_INT);
			$q->bindValue(':bestelId', $bestelId, PDO::PARAM_INT);
			$q->execute();
		}
		$totaal = $this->getBestellingTotaal($bestelId);
		$q = $this->db->prepare(
			'UPDATE civi_saldo SET saldo = saldo - :totaal, laatst_veranderd = :laatstVeranderd WHERE uid=:socCieId ;'
		);
		$q->bindValue(':totaal', $totaal, PDO::PARAM_INT);
		$q->bindValue(':laatstVeranderd', DateUtil::getDateTime());

		$q->bindValue(':socCieId', $data->persoon->socCieId, PDO::PARAM_STR);
		$q->execute();
		$q = $this->db->prepare(
			'UPDATE civi_bestelling  SET totaal = :totaal WHERE id = :bestelId;'
		);
		$q->bindValue(':totaal', $totaal, PDO::PARAM_INT);
		$q->bindValue(':bestelId', $bestelId, PDO::PARAM_INT);
		$q->execute();

		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}
		return true;
	}

	private function getBestellingTotaal($bestelId)
	{
		$q = $this->db->prepare(
			'SELECT SUM(prijs * aantal) FROM civi_bestelling_inhoud AS I JOIN civi_prijs AS P USING (product_id) WHERE bestelling_id = :bestelId AND tot IS NULL'
		);
		$q->bindValue(':bestelId', $bestelId, PDO::PARAM_INT);
		$q->execute();
		return $q->fetchColumn();
	}

	public function getBestellingLaatste(
		$persoon,
		\DateTimeImmutable $begin = null,
		\DateTimeImmutable $eind = null,
		$productIDs = []
	) {
		if ($begin == null) {
			$begin = date_create_immutable()->add(new \DateInterval('P15H'));
		} else {
			$begin = $begin->setTime(0, 0, 0);
		}
		if ($eind == null) {
			$eind = date_create_immutable();
		} else {
			$eind = $eind->setTime(23, 59, 59);
		}

		if ($persoon == 'alles') {
			return $this->civiBestellingRepository->findTussen($begin, $eind, [
				'soccie',
				'oweecie',
			]);
		} else {
			return $this->civiBestellingRepository->findTussen(
				$begin,
				$eind,
				['soccie', 'oweecie'],
				$persoon
			);
		}
	}

	/**
	 * @param string $uid
	 * @param integer $bestelId
	 * @param $inhoud
	 */
	public function updateBestelling($uid, $bestelId, $inhoud)
	{
		$em = $this->entityManager;

		$em->transactional(function () use ($em, $uid, $bestelId, $inhoud) {
			$civiBestelling = $this->civiBestellingRepository->find($bestelId);
			$civiSaldo = $this->civiSaldoRepository->find($uid);

			// Voeg totaal van bestelling toe aan CiviSaldo
			$civiSaldo->saldo += $civiBestelling->berekenTotaal();

			$em->flush();

			// Verwijder de inhoud van de oude bestelling.

			foreach ($civiBestelling->inhoud as $item) {
				$item->setBestelling(null);
				$em->remove($item);
			}

			$civiBestelling->inhoud->clear();
			$civiBestelling->totaal = 0;

			$em->flush();

			// Voeg de inhoud van de bestelling toe.

			foreach ($inhoud as $id => $aantal) {
				$bestellinInhoud = new CiviBestellingInhoud();
				$bestellinInhoud->setProduct($this->civiProductRepository->find($id));
				$bestellinInhoud->aantal = $aantal;
				$bestellinInhoud->setBestelling($civiBestelling);

				$em->persist($bestellinInhoud);

				$civiBestelling->inhoud->add($bestellinInhoud);
			}

			$em->flush();

			// Update het totaal van de CiviBestelling
			$civiBestelling->totaal = $civiBestelling->berekenTotaal();

			// Trek totaal van het saldo af.

			$civiSaldo->saldo -= $civiBestelling->totaal;

			$em->flush();
		});
	}

	public function getSaldo($socCieId)
	{
		$q = $this->db->prepare(
			'SELECT saldo FROM civi_saldo WHERE uid = :socCieId'
		);
		$q->bindValue(':socCieId', $socCieId);
		$q->execute();
		return $q->fetchColumn();
	}

	public function verwijderBestelling($bestelId)
	{
		$em = $this->entityManager;

		$em->transactional(function () use ($em, $bestelId) {
			$civiBestelling = $this->civiBestellingRepository->find($bestelId);

			if ($civiBestelling->deleted) {
				throw new CsrGebruikerException('Bestelling is al verwijderd');
			}

			$civiSaldo = $civiBestelling->civiSaldo;

			$civiSaldo->saldo += $civiBestelling->totaal;

			$civiBestelling->deleted = true;

			$em->flush();
		});
	}

	public function undoVerwijderBestelling($bestelId)
	{
		$em = $this->entityManager;

		$em->transactional(function () use ($em, $bestelId) {
			$civiBestelling = $this->civiBestellingRepository->find($bestelId);

			if (!$civiBestelling->deleted) {
				throw new CsrGebruikerException('Bestelling is niet verwijderd');
			}

			$civiSaldo = $civiBestelling->civiSaldo;

			$civiSaldo->saldo -= $civiBestelling->totaal;

			$civiBestelling->deleted = false;

			$em->flush();
		});
	}

	// Beheer

	public function getGrootboekInvoer()
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

	public function getToolData()
	{
		$data = [];

		$data['sum_saldi'] = $this->sumSaldi();
		$data['sum_saldi_lid'] = $this->sumSaldi(true);
		$data['red'] = $this->getRed();

		return $data;
	}

	private function sumSaldi($profielOnly = false)
	{
		$after = $profielOnly ? "AND uid NOT LIKE 'c%'" : '';

		return $this->db
			->query(
				'SELECT SUM(saldo) AS sum FROM civi_saldo WHERE deleted = 0 ' . $after
			)
			->fetch(PDO::FETCH_ASSOC);
	}

	private function getRed()
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

	public function addProduct($name, $price, $type)
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

	public function updatePerson($id, $name)
	{
		$civiSaldo = $this->civiSaldoRepository->find($id);
		$civiSaldo->naam = $name;

		$this->civiSaldoRepository->update($civiSaldo);
	}

	public function removePerson($id)
	{
		$q = $this->db->prepare(
			'UPDATE civi_saldo SET deleted = 1 WHERE uid = :id AND saldo = 0'
		);
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		$q->execute();
		return $q->rowCount();
	}

	public function addPerson($name, $saldo, $uid)
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

	public function updatePrice($productId, $price)
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

	public function updateVisibility($productId, $visibility)
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
	public function log($type, $data)
	{
		$value = [];
		foreach ($data as $key => $item) {
			$value[] = $key . ' = ' . $item;
		}
		$value = implode("\r\n", $value);

		$q = $this->db->prepare(
			'INSERT INTO civi_saldo_log (ip, type, data) VALUES(:ip, :type, :data)'
		);
		$q->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$q->bindValue(':type', $type, PDO::PARAM_STR);
		$q->bindValue(':data', $value, PDO::PARAM_STR);
		$q->execute();
	}
}
