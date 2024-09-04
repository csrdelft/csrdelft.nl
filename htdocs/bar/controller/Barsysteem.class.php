<?php

use CsrDelft\common\Util\DateUtil;
use CsrDelft\model\entity\LidStatus;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;

class Barsysteem {

	/**
	 * @var Connection
	 */
	public $db;
	private $beheer;
	private $csrfToken;

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	function __construct() {
		$this->db = DriverManager::getConnection([
			'url' => $_ENV['DATABASE_URL'],
			'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"]
		])->getWrappedConnection();
	}

	function isLoggedIn() {
		return isset($_COOKIE['barsysteem']) && md5('my_salt_is_strong' . $_COOKIE['barsysteem']) == '8f700ce34a77ef4ef9db9bbdde9e97d8';
	}

	function isBeheer() {
		if (!$this->beheer)
			$this->beheer = isset($_COOKIE['barsysteembeheer']) && md5('my_salt_is_strong' . $_COOKIE['barsysteembeheer']) == '5367b4668337c47a02cf87793a6a05d5';

		return $this->beheer;
	}

	function getCsrfToken() {
		if ($this->csrfToken === null) {
			if ($this->isBeheer()) {
				$this->csrfToken = md5('Barsysteem CSRF-token C.S.R. Delft' . $_COOKIE['barsysteembeheer']);
			} else {
				$this->csrfToken = md5('Barsysteem CSRF-token C.S.R. Delft' . $_COOKIE['barsysteem']);
			}
		}
		return $this->csrfToken;
	}

	public function preventCsrf() {
		$token = null;
		if (isset($_SERVER['HTTP_X_BARSYSTEEM_CSRF'])) {
			$token = $_SERVER['HTTP_X_BARSYSTEEM_CSRF'];
		} else if (isset($_POST["X-BARSYSTEEM-CSRF"])) {
			$token = $_POST("X-BARSYSTEEM-CSRF");
		}
		return $token != null && $this->getCsrfToken() === $token;
	}

	function getProfiel($uid) {
		$q = $this->db->prepare(<<<SQL
SELECT profielen.voornaam, profielen.voorletters, profielen.tussenvoegsel, profielen.achternaam, profielen.status, profielen.email, accounts.email as accountEmail
FROM profielen LEFT JOIN accounts ON accounts.uid = profielen.uid
WHERE profielen.uid = :uid
SQL
		);
		$q->bindValue('uid', $uid);

		return $q->execute()->fetchAssociative();
	}

	function getNaam($profiel) {

		if (empty($profiel["voornaam"])) {
			$naam = $profiel["voorletters"] . ' ';
		} else {
			$naam = $profiel["voornaam"] . ' ';
		}
		if (!empty($profiel["tussenvoegsel"])) {
			$naam .= $profiel["tussenvoegsel"] . ' ';
		}
		$naam .= $profiel["achternaam"];

		return $naam;
	}

	/**
	 * @throws \Doctrine\DBAL\Driver\Exception
	 */
	function getPersonen() {
		$terug = $this->db->query(<<<SQL
SELECT civi_saldo.uid, civi_saldo.naam, civi_saldo.saldo, civi_saldo.deleted, COUNT(civi_bestelling.totaal) AS recent
FROM civi_saldo LEFT JOIN civi_bestelling
ON (civi_saldo.uid = civi_bestelling.uid AND DATEDIFF(NOW(), civi_bestelling.moment) < 100 AND civi_bestelling.deleted = 0)
GROUP BY civi_saldo.uid;
SQL
		)->fetchAllAssociative();
		$result = [];
		foreach ($terug as $row) {
			$persoon = [];
			$persoon["naam"] = $row["naam"];
			$persoon["status"] = LidStatus::Nobody;
			if ($row["uid"]) {
				$profiel = $this->getProfiel($row["uid"]);
				if ($profiel) {
					$persoon["naam"] = $this->getNaam($profiel);
					$persoon["status"] = $profiel["status"];
				}
			}
			$persoon["socCieId"] = $row["uid"];
			$persoon["bijnaam"] = $row["naam"];
			$persoon["saldo"] = $row["saldo"];
			$persoon["recent"] = $row["recent"];
			$persoon["deleted"] = $row["deleted"];
			$result[$row["uid"]] = $persoon;
		}
		return $result;
	}

	function getProducten() {
		$q = $this->db->prepare(<<<SQL
SELECT P.id, beheer, prijs, beschrijving, prioriteit, P.status, C.cie
FROM civi_product AS P
JOIN civi_prijs AS R
ON (P.id=R.product_id AND CURRENT_TIMESTAMP > van AND tot IS NULL)
JOIN civi_categorie AS C
ON (C.id=P.categorie_id)
WHERE C.cie = 'soccie' OR C.cie = 'oweecie'
ORDER BY prioriteit DESC
SQL
		);

		$result = [];
		foreach ($q->execute()->fetchAllAssociative() as $row) {
			$product = [];
			$product["productId"] = $row["id"];
			$product["prijs"] = $row["prijs"];
			$product["beheer"] = $row["beheer"];
			$product["beschrijving"] = $row["beschrijving"];
			$product["prioriteit"] = $row["prioriteit"];
			$product["status"] = $row["status"];
			$product["cie"] = $row["cie"];
			$result[$row["id"]] = $product;
		}
		return $result;
	}

	/**
	 * @throws \Doctrine\DBAL\Driver\Exception
	 */
	function getGrootboeken() {
		$q = $this->db->prepare("SELECT id, type FROM civi_categorie WHERE cie='soccie'");
		return $q->execute()->fetchAllAssociative();
	}

	/**
	 * This function should only be here temporarily, if this is still here after the 2017 OWee,
	 * burn this code like they would a heretic in the middle ages.
	 */
	function verwerkBestelling($data)
	{
		$producten = $this->getProducten();
		$soccieBestelling = (object) ['bestelLijst' => [], 'bestelTotaal' => 0, 'persoon' => $data->persoon];
		$oweecieBestelling = (object) ['bestelLijst' => [], 'bestelTotaal' => 0, 'persoon' => $data->persoon];
		foreach ($data->bestelLijst as $productId => $aantal) {
			switch ($producten[$productId]['cie']) {
				case 'oweecie':
					$oweecieBestelling->bestelLijst[$productId] = $aantal;
					break;
				case 'soccie':
				default:
					$soccieBestelling->bestelLijst[$productId] = $aantal;
					break;
			}
		}
		$success = true;
		if (!empty($soccieBestelling->bestelLijst)) {
			$success = $success && $this->verwerkBestellingVoorCommissie($soccieBestelling, 'soccie');
		}
		if (!empty($oweecieBestelling->bestelLijst)) {
			$success = $success && $this->verwerkBestellingVoorCommissie($oweecieBestelling, 'oweecie');
		}
		return $success;
	}

	function verwerkBestellingVoorCommissie($data, $cie = 'soccie') {
		$this->db->beginTransaction();

		$q = $this->db->prepare("INSERT INTO civi_bestelling (uid, cie, totaal) VALUES (:socCieId, :commissie, 0);");
		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->bindValue(":commissie", $cie, PDO::PARAM_STR);
		$q->execute();
		$bestelId = $this->db->lastInsertId();
		foreach ($data->bestelLijst as $productId => $aantal) {
			$q = $this->db->prepare("INSERT INTO civi_bestelling_inhoud VALUES (:bestelId,  :productId, :aantal);");
			$q->bindValue(":productId", $productId, PDO::PARAM_INT);
			$q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
			$q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
			$q->execute();
		}
		$totaal = $this->getBestellingTotaal($bestelId);
		$q = $this->db->prepare("UPDATE civi_saldo SET saldo = saldo - :totaal, laatst_veranderd = :laatstVeranderd WHERE uid=:socCieId ;");
		$q->bindValue(":totaal", $totaal, PDO::PARAM_INT);
		$q->bindValue(":laatstVeranderd", DateUtil::getDateTime());

		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->execute();
		$q = $this->db->prepare("UPDATE civi_bestelling  SET totaal = :totaal WHERE id = :bestelId;");
		$q->bindValue(":totaal", $totaal, PDO::PARAM_INT);
		$q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
		$q->execute();

		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}
		return true;
	}

	function getBestellingPersoon($socCieId) {
		$q = $this->db->prepare("SELECT *, B.deleted AS d, 0 AS oud FROM civi_bestelling AS B JOIN civi_bestelling_inhoud AS I ON B.id=I.bestelling_id WHERE uid=:socCieId AND B.cie = 'soccie' OR B.cie = 'oweecie'");
		$q->bindValue(":socCieId", $socCieId, PDO::PARAM_STR);
		return $this->verwerkBestellingResultaat($q->execute()->fetchAllAssociative());
	}

	function getBestellingLaatste($persoon, $begin, $eind, $productType) {
		$productIDs = [];
		foreach ($productType as $product) {
			$productIDs[] = $product['value'];
		}

		if ($begin == "") {
			$begin = DateUtil::getDateTime(time() - 15 * 3600);
		} else {
			$begin = $this->parseDate($begin) . " 00:00:00";
		}
		if ($eind == "") {
			$eind = DateUtil::getDateTime();
		} else {
			$eind = $this->parseDate($eind) . " 23:59:59";
		}
		$qa = "";
		if ($persoon != "alles")
			$qa = "B.uid=:socCieId AND";
		$q = $this->db->prepare(<<<SQL
SELECT *, B.deleted AS d, K.deleted AS oud
FROM civi_bestelling AS B
JOIN civi_bestelling_inhoud AS I
ON B.id=I.bestelling_id
JOIN civi_saldo AS K
USING (uid)
WHERE (B.cie = 'soccie' OR B.cie = 'oweecie') AND $qa (moment BETWEEN :begin AND :eind)
SQL
		);
		if ($persoon != "alles")
			$q->bindValue(":socCieId", $persoon, PDO::PARAM_STR);
		$q->bindValue(":begin", $begin);
		$q->bindValue(":eind", $eind);
		return $this->verwerkBestellingResultaat($q->execute()->fetchAllAssociative(), $productIDs);
	}

	function updateBestelling($data) {

		$this->db->beginTransaction();

		// Add old order to saldo
		$q = $this->db->prepare("UPDATE civi_saldo SET saldo = saldo + :bestelTotaal WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->execute();

		// Remove old contents of the order
		$q = $this->db->prepare("DELETE FROM civi_bestelling_inhoud WHERE bestelling_id = :bestelId");
		$q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
		$q->execute();

		// Add contents of the order
		foreach ($data->bestelLijst as $productId => $aantal) {
			$q = $this->db->prepare("INSERT INTO civi_bestelling_inhoud VALUES (:bestelId, :productId, :aantal);");
			$q->bindValue(":productId", $productId, PDO::PARAM_INT);
			$q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
			$q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
			$q->execute();
		}

		// Substract new order from saldo
		$q = $this->db->prepare("UPDATE civi_saldo SET saldo = saldo - :bestelTotaal, laatst_veranderd = :laatstVeranderd WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
		$q->bindValue(":laatstVeranderd", DateUtil::getDateTime());
		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->execute();

		// Update old order
		$q = $this->db->prepare("UPDATE civi_bestelling SET totaal = :totaal WHERE id = :bestelId");
		$q->bindValue(":totaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
		$q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
		$q->execute();

		// Roll back if error
		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}
		return true;
	}

	function getSaldo($socCieId) {
		$q = $this->db->prepare("SELECT saldo FROM civi_saldo WHERE uid = :socCieId");
		$q->bindValue(":socCieId", $socCieId);
		return $q->execute()->fetchOne();
	}

	function verwijderBestelling($data) {
		$this->db->beginTransaction();
		$q = $this->db->prepare("UPDATE civi_saldo SET saldo = saldo + :bestelTotaal WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
		$q->bindValue(":socCieId", $data->persoon, PDO::PARAM_STR);
		$q->execute();
		$q = $this->db->prepare("UPDATE civi_bestelling SET deleted = 1 WHERE id = :bestelId AND deleted = 0");
		$q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
		$result = $q->execute();

		if (!$this->db->commit() || $result->rowCount() == 0) {
			$this->db->rollBack();
			return false;
		}
		return true;
	}

	/**
	 * @throws \Doctrine\DBAL\Driver\Exception
	 */
	function undoVerwijderBestelling($data) {
		$this->db->beginTransaction();
		$q = $this->db->prepare("UPDATE civi_saldo SET saldo = saldo - :bestelTotaal WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
		$q->bindValue(":socCieId", $data->persoon, PDO::PARAM_STR);
		$q->execute();
		$q = $this->db->prepare("UPDATE civi_bestelling SET deleted = 0 WHERE id = :bestelId AND deleted = 1");
		$q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
		$result = $q->execute();
		if (!$this->db->commit() || $result->rowCount() == 0) {
			$this->db->rollBack();
			return false;
		}
		return true;
	}

	private function verwerkBestellingResultaat($queryResult, $productIDs = []) {
		$result = [];
		foreach ($queryResult as $row) {
			if (!array_key_exists($row["bestelling_id"], $result)) {
				$result[$row["bestelling_id"]] = [];
				$result[$row["bestelling_id"]]["bestelLijst"] = [];
				$result[$row["bestelling_id"]]["bestelTotaal"] = $row["totaal"];
				$result[$row["bestelling_id"]]["persoon"] = $row["uid"];
				$result[$row["bestelling_id"]]["tijd"] = $row["moment"];
				$result[$row["bestelling_id"]]["bestelId"] = $row["bestelling_id"];
				$result[$row["bestelling_id"]]["deleted"] = $row["d"];
				$result[$row["bestelling_id"]]["oud"] = $row["oud"];
			}
			$result[$row["bestelling_id"]]["bestelLijst"][$row["product_id"]] = 1 * $row["aantal"];
		}

		if (!empty($productIDs)) {
			foreach ($result as $key => $bestelling) {

				$keep = false;
				foreach ($productIDs as $id) {
					if (in_array($id, array_keys($bestelling["bestelLijst"]))) {
						$keep = true;
					}
				}

				if (!$keep)
					unset($result[$key]);
			}
		}

		return $result;
	}

	private function getBestellingTotaal($bestelId) {
		$q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM civi_bestelling_inhoud AS I JOIN civi_prijs AS P USING (product_id) WHERE bestelling_id = :bestelId AND tot IS NULL");
		$q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
		return $q->execute()->fetchOne();
	}

	private function getBestellingTotaalTijd($bestelId, $timestamp) {
		$q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM civi_bestelling_inhoud AS I JOIN civi_prijs AS P USING (product_id) WHERE bestelling_id = :bestelId AND (:timeStamp > P.van AND (:timeStamp < P.tot OR P.tot IS NULL));");
		$q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
		$q->bindValue(":timeStamp", $timestamp, PDO::PARAM_STR);
		return $q->execute()->fetchOne();
	}

	private function parseDate($date) {
		$elementen = explode(" ", (string) $date);
		$datum = str_pad($elementen[0], 2, "0", STR_PAD_LEFT);
		$maanden = ["Januari" => "01", "Februari" => "02", "Maart" => "03", "April" => "04", "Mei" => "05", "Juni" => "06", "Juli" => "07", "Augustus" => "08", "September" => "09", "Oktober" => "10", "November" => "11", "December" => "12"];
		return ($elementen[2] . "-" . $maanden[$elementen[1]] . "-" . $datum);
	}

	// Beheer

	/**
	 * @throws \Doctrine\DBAL\Driver\Exception
	 */
	public function getGrootboekInvoer() {

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
		$result = $q->execute();

		$weeks = [];

		while ($r = $result->fetchAssociative()) {

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

	public function getToolData() {

		$data = [];

		$data['sum_saldi'] = $this->sumSaldi();
		$data['sum_saldi_lid'] = $this->sumSaldi(true);
		$data['red'] = $this->getRed();

		return $data;
	}

	/**
	 * @throws \Doctrine\DBAL\Driver\Exception
	 */
	private function sumSaldi($profielOnly = false) {
		$result = $profielOnly ?
			$this->db->query("SELECT SUM(saldo) AS sum FROM civi_saldo WHERE deleted = 0 AND uid NOT LIKE 'c%'") :
			$this->db->query("SELECT SUM(saldo) AS sum FROM civi_saldo WHERE deleted = 0");

		return $result->fetchAssociative();
	}

	private function getRed() {

		$result = [];

		$q = $this->db->query("SELECT uid, saldo FROM civi_saldo WHERE deleted = 0 AND saldo < 0 AND uid NOT LIKE 'c%' ORDER BY saldo");
		while ($r = $q->fetchAssociative()) {

			$profiel = $this->getProfiel($r['uid']);

			$result[] = [
       'naam' => $this->getNaam($profiel),
       'email' => $profiel['accountEmail'] ?? $profiel['email'],
       // ?: "rood" ?? $profiel['accountEmail'],
       'saldo' => $r['saldo'],
       'status' => $profiel['status'],
   ];
		}

		return $result;
	}

	public function addProduct($name, $price, $type) {

		if ($type < 1)
			return false;

		$this->db->beginTransaction();

		$q = $this->db->prepare("INSERT INTO civi_product(status, beschrijving, prioriteit, categorie_id, beheer) VALUES(1, :name, -5000, :type, 0)");
		$q->bindValue(':name', $name);
		$q->bindValue(':type', $type);
		$q->execute();

		$q = $this->db->prepare("INSERT INTO civi_prijs(product_id, prijs) VALUES(:productId, :price)");
		$q->bindValue(':productId', $this->db->lastInsertId());
		$q->bindValue(':price', $price);
		$q->execute();

		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}

		return true;
	}

	public function updatePerson($id, $name) {

		$q = $this->db->prepare("UPDATE civi_saldo SET naam = :naam WHERE uid = :id");
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		$q->bindValue(':naam', $name, PDO::PARAM_STR);
		return $q->execute();
	}

	/**
	 * @throws \Doctrine\DBAL\Driver\Exception
	 */
	public function removePerson($id) {

		$q = $this->db->prepare("UPDATE civi_saldo SET deleted = 1 WHERE uid = :id AND saldo = 0");
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		return $q->execute()->rowCount();
	}

	public function addPerson($name, $saldo, $uid) {

		$q = $this->db->prepare("INSERT INTO civi_saldo (naam, saldo, uid) VALUES (:naam, :saldo, :uid)");
		$q->bindValue(':naam', $name, PDO::PARAM_STR);
		$q->bindValue(':saldo', $saldo, PDO::PARAM_STR);
		if (!empty($uid)) {
			$q->bindValue(':uid', $uid, PDO::PARAM_STR);
		} else {
			$latest = $this->db->query("SELECT uid FROM civi_saldo WHERE uid LIKE 'c%' ORDER BY uid DESC LIMIT 1")->fetchFirstColumn()[0];
			$q->bindValue(':uid', ++$latest, PDO::PARAM_STR);
		}

		return $q->execute();
	}

	public function updatePrice($productId, $price) {

		$this->db->beginTransaction();

		$q = $this->db->prepare("UPDATE civi_prijs SET tot = CURRENT_TIMESTAMP WHERE product_id = :productId AND tot IS NULL ORDER BY van DESC LIMIT 1");
		$q->bindValue(':productId', $productId);
		$q->execute();

		$q = $this->db->prepare("INSERT INTO civi_prijs (product_id, prijs) VALUES (:productId, :prijs)");
		$q->bindValue(':productId', $productId);
		$q->bindValue(':prijs', $price);
		$q->execute();

		if (!$this->db->commit()) {
			$this->db->rollBack();
			return false;
		}

		return true;
	}

	public function updateVisibility($productId, $visibility) {

		$this->db->beginTransaction();

		$q = $this->db->prepare("UPDATE civi_product SET status = :visibility WHERE id = :productId");
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
	public function log($type, $data) {
		$value = [];
		foreach ($data as $key => $item) {

			$value[] = $key . ' = ' . $item;
		}
		$value = implode("\r\n", $value);

		$q = $this->db->prepare("INSERT INTO civi_saldo_log (ip, type, data) VALUES(:ip, :type, :data)");
		$q->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$q->bindValue(':type', $type, PDO::PARAM_STR);
		$q->bindValue(':data', $value, PDO::PARAM_STR);
		$q->execute();
	}

}
