<?php

use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\Orm\Persistence\Database;

class Barsysteem {

	var $db;
	private $beheer;
	private $csrfToken;
	function __construct() {
		$this->db = Database::instance()->getDatabase();
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

	function getNaam($profiel) {

		if (empty($profiel->voornaam)) {
			$naam = $profiel->voorletters . ' ';
		} else {
			$naam = $profiel->voornaam . ' ';
		}
		if (!empty($profiel->tussenvoegsel)) {
			$naam .= $profiel->tussenvoegsel . ' ';
		}
		$naam .= $profiel->achternaam;

		return $naam;
	}

	function getPersonen() {
		$terug = $this->db->query(<<<SQL
SELECT CiviSaldo.uid, CiviSaldo.naam, CiviSaldo.saldo, CiviSaldo.deleted, COUNT(CiviBestelling.totaal) AS recent
FROM CiviSaldo LEFT JOIN CiviBestelling
ON (CiviSaldo.uid = CiviBestelling.uid AND DATEDIFF(NOW(), CiviBestelling.moment) < 100 AND CiviBestelling.deleted = 0)
GROUP BY CiviSaldo.id;
SQL
		);
		$result = array();
		foreach ($terug as $row) {
			$persoon = array();
			$persoon["naam"] = $row["naam"];
			$persoon["status"] = LidStatus::Nobody;
			if ($row["uid"]) {
				$profiel = ProfielRepository::get($row["uid"]);
				if ($profiel) {
					$persoon["naam"] = $this->getNaam($profiel);
					$persoon["status"] = $profiel->status;
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
FROM CiviProduct AS P
JOIN CiviPrijs AS R
ON (P.id=R.product_id AND CURRENT_TIMESTAMP > van AND tot IS NULL)
JOIN CiviCategorie AS C
ON (C.id=P.categorie_id)
WHERE C.cie = 'soccie' OR C.cie = 'oweecie'
ORDER BY prioriteit DESC
SQL
		);
		$q->execute();

		$result = array();
		foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
			$product = array();
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

	function getGrootboeken() {
		$q = $this->db->prepare("SELECT id, type FROM CiviCategorie WHERE cie='soccie'");
		$q->execute();
		return $q->fetchAll(PDO::FETCH_ASSOC);
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

		$q = $this->db->prepare("INSERT INTO CiviBestelling (uid, cie, totaal) VALUES (:socCieId, :commissie, 0);");
		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->bindValue(":commissie", $cie, PDO::PARAM_STR);
		$q->execute();
		$bestelId = $this->db->lastInsertId();
		foreach ($data->bestelLijst as $productId => $aantal) {
			$q = $this->db->prepare("INSERT INTO CiviBestellingInhoud VALUES (:bestelId,  :productId, :aantal);");
			$q->bindValue(":productId", $productId, PDO::PARAM_INT);
			$q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
			$q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
			$q->execute();
		}
		$totaal = $this->getBestellingTotaal($bestelId);
		$q = $this->db->prepare("UPDATE CiviSaldo SET saldo = saldo - :totaal, laatst_veranderd = :laatstVeranderd WHERE uid=:socCieId ;");
		$q->bindValue(":totaal", $totaal, PDO::PARAM_INT);
		$q->bindValue(":laatstVeranderd", getDateTime());

		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->execute();
		$q = $this->db->prepare("UPDATE CiviBestelling  SET totaal = :totaal WHERE id = :bestelId;");
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
		$q = $this->db->prepare("SELECT *, B.deleted AS d, 0 AS oud FROM CiviBestelling AS B JOIN CiviBestellingInhoud AS I ON B.id=I.bestelling_id WHERE uid=:socCieId AND B.cie = 'soccie' OR B.cie = 'oweecie'");
		$q->bindValue(":socCieId", $socCieId, PDO::PARAM_STR);
		$q->execute();
		return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC));
	}

	function getBestellingLaatste($persoon, $begin, $eind, $productType) {
		$productIDs = array();
		foreach ($productType as $product) {
			$productIDs[] = $product['value'];
		}

		if ($begin == "") {
			$begin = getDateTime(time() - 15 * 3600);
		} else {
			$begin = $this->parseDate($begin) . " 00:00:00";
		}
		if ($eind == "") {
			$eind = getDateTime();
		} else {
			$eind = $this->parseDate($eind) . " 23:59:59";
		}
		$qa = "";
		if ($persoon != "alles")
			$qa = "B.uid=:socCieId AND";
		$q = $this->db->prepare(<<<SQL
SELECT *, B.deleted AS d, K.deleted AS oud
FROM CiviBestelling AS B
JOIN CiviBestellingInhoud AS I
ON B.id=I.bestelling_id
JOIN CiviSaldo AS K
USING (uid)
WHERE (B.cie = 'soccie' OR B.cie = 'oweecie') AND $qa (moment BETWEEN :begin AND :eind)
SQL
		);
		if ($persoon != "alles")
			$q->bindValue(":socCieId", $persoon, PDO::PARAM_STR);
		$q->bindValue(":begin", $begin);
		$q->bindValue(":eind", $eind);
		$q->execute();
		return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC), $productIDs);
	}

	function updateBestelling($data) {

		$this->db->beginTransaction();

		// Add old order to saldo
		$q = $this->db->prepare("UPDATE CiviSaldo SET saldo = saldo + :bestelTotaal WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->execute();

		// Remove old contents of the order
		$q = $this->db->prepare("DELETE FROM CiviBestellingInhoud WHERE bestelling_id = :bestelId");
		$q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
		$q->execute();

		// Add contents of the order
		foreach ($data->bestelLijst as $productId => $aantal) {
			$q = $this->db->prepare("INSERT INTO CiviBestellingInhoud VALUES (:bestelId, :productId, :aantal);");
			$q->bindValue(":productId", $productId, PDO::PARAM_INT);
			$q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
			$q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
			$q->execute();
		}

		// Substract new order from saldo
		$q = $this->db->prepare("UPDATE CiviSaldo SET saldo = saldo - :bestelTotaal, laatst_veranderd = :laatstVeranderd WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
		$q->bindValue(":laatstVeranderd", getDateTime());
		$q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_STR);
		$q->execute();

		// Update old order
		$q = $this->db->prepare("UPDATE CiviBestelling SET totaal = :totaal WHERE id = :bestelId");
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
		$q = $this->db->prepare("SELECT saldo FROM CiviSaldo WHERE uid = :socCieId");
		$q->bindValue(":socCieId", $socCieId);
		$q->execute();
		return $q->fetchColumn();
	}

	function verwijderBestelling($data) {
		$this->db->beginTransaction();
		$q = $this->db->prepare("UPDATE CiviSaldo SET saldo = saldo + :bestelTotaal WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
		$q->bindValue(":socCieId", $data->persoon, PDO::PARAM_STR);
		$q->execute();
		$q = $this->db->prepare("UPDATE CiviBestelling SET deleted = 1 WHERE id = :bestelId AND deleted = 0");
		$q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
		$q->execute();
		if (!$this->db->commit() || $q->rowCount() == 0) {
			$this->db->rollBack();
			return false;
		}
		return true;
	}

	function undoVerwijderBestelling($data) {
		$this->db->beginTransaction();
		$q = $this->db->prepare("UPDATE CiviSaldo SET saldo = saldo - :bestelTotaal WHERE uid=:socCieId;");
		$q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
		$q->bindValue(":socCieId", $data->persoon, PDO::PARAM_STR);
		$q->execute();
		$q = $this->db->prepare("UPDATE CiviBestelling SET deleted = 0 WHERE id = :bestelId AND deleted = 1");
		$q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
		$q->execute();
		if (!$this->db->commit() || $q->rowCount() == 0) {
			$this->db->rollBack();
			return false;
		}
		return true;
	}

	private function verwerkBestellingResultaat($queryResult, $productIDs = array()) {
		$result = array();
		foreach ($queryResult as $row) {
			if (!array_key_exists($row["bestelling_id"], $result)) {
				$result[$row["bestelling_id"]] = array();
				$result[$row["bestelling_id"]]["bestelLijst"] = array();
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
		$q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM CiviBestellingInhoud AS I JOIN CiviPrijs AS P USING (product_id) WHERE bestelling_id = :bestelId AND tot IS NULL");
		$q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
		$q->execute();
		return $q->fetchColumn();
	}

	private function getBestellingTotaalTijd($bestelId, $timestamp) {
		$q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM CiviBestellingInhoud AS I JOIN CiviPrijs AS P USING (product_id) WHERE bestelling_id = :bestelId AND (:timeStamp > P.van AND (:timeStamp < P.tot OR P.tot IS NULL));");
		$q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
		$q->bindValue(":timeStamp", $timestamp, PDO::PARAM_STMT);
		$q->execute();
		return $q->fetchColumn();
	}

	private function parseDate($date) {
		$elementen = explode(" ", $date);
		$datum = str_pad($elementen[0], 2, "0", STR_PAD_LEFT);
		$maanden = ["Januari" => "01", "Februari" => "02", "Maart" => "03", "April" => "04", "Mei" => "05", "Juni" => "06", "Juli" => "07", "Augustus" => "08", "September" => "09", "Oktober" => "10", "November" => "11", "December" => "12"];
		return ($elementen[2] . "-" . $maanden[$elementen[1]] . "-" . $datum);
	}

	// Beheer
	public function getGrootboekInvoer() {

		// GROUP BY week
		$q = $this->db->prepare("
SELECT G.type,
	SUM(I.aantal * PR.prijs) AS total,
	WEEK(B.moment, 3) AS week,
	YEARWEEK(B.moment, 3) AS yearweek
FROM CiviBestelling AS B
JOIN CiviBestellingInhoud AS I ON
	B.id = I.bestelling_id
JOIN CiviProduct AS P ON
	I.product_id = P.id
JOIN CiviPrijs AS PR ON
	P.id = PR.product_id
	AND (B.moment > PR.van AND (B.moment < PR.tot OR PR.tot IS NULL))
JOIN CiviCategorie AS G ON
	P.categorie_id = G.id
WHERE
	B.deleted = 0 AND
	G.status = 1 AND
	B.cie = 'soccie'
GROUP BY
	yearweek,
	G.id
ORDER BY yearweek DESC
		");
		$q->execute();

		$weeks = array();

		while ($r = $q->fetch(PDO::FETCH_ASSOC)) {

			$exists = isset($weeks[$r['yearweek']]);

			$week = $exists ? $weeks[$r['yearweek']] : array();

			if ($exists) {
				$week['content'][] = array('type' => $r['type'], 'total' => $r['total']);
			} else {
				$week['content'] = array(array('type' => $r['type'], 'total' => $r['total']));
				$week['title'] = 'Week ' . $r['week'];
			}

			$weeks[$r['yearweek']] = $week;
		}

		return $weeks;
	}

	public function getToolData() {

		$data = array();

		$data['sum_saldi'] = $this->sumSaldi();
		$data['sum_saldi_lid'] = $this->sumSaldi(true);
		$data['red'] = $this->getRed();

		return $data;
	}

	private function sumSaldi($profielOnly = false) {

		$after = $profielOnly ? "AND uid NOT LIKE 'c%'" : "";

		return $this->db->query("SELECT SUM(saldo) AS sum FROM CiviSaldo WHERE deleted = 0 " . $after)->fetch(PDO::FETCH_ASSOC);
	}

	private function getRed() {

		$result = array();

		$q = $this->db->query("SELECT uid, saldo FROM CiviSaldo WHERE deleted = 0 AND saldo < 0 AND uid NOT LIKE 'c%' ORDER BY saldo");
		while ($r = $q->fetch(PDO::FETCH_ASSOC)) {

			$result[] = array(
				'naam' => $this->getNaam(ProfielRepository::get($r['uid'])),
				'email' => ProfielRepository::get($r['uid'])->getPrimaryEmail(),
				'saldo' => $r['saldo'],
				'status' => ProfielRepository::get($r['uid'])->status
			);
		}

		return $result;
	}

	public function addProduct($name, $price, $type) {

		if ($type < 1)
			return false;

		$this->db->beginTransaction();

		$q = $this->db->prepare("INSERT INTO CiviProduct(status, beschrijving, prioriteit, categorie_id, beheer) VALUES(1, :name, -5000, :type, 0)");
		$q->bindValue(':name', $name);
		$q->bindValue(':type', $type);
		$q->execute();

		$q = $this->db->prepare("INSERT INTO CiviPrijs(product_id, prijs) VALUES(:productId, :price)");
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

		$q = $this->db->prepare("UPDATE CiviSaldo SET naam = :naam WHERE uid = :id");
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		$q->bindValue(':naam', $name, PDO::PARAM_STR);
		return $q->execute();
	}

	public function removePerson($id) {

		$q = $this->db->prepare("UPDATE CiviSaldo SET deleted = 1 WHERE uid = :id AND saldo = 0");
		$q->bindValue(':id', $id, PDO::PARAM_STR);
		$q->execute();
		return $q->rowCount();
	}

	public function addPerson($name, $saldo, $uid) {

		$q = $this->db->prepare("INSERT INTO CiviSaldo (naam, saldo, uid) VALUES (:naam, :saldo, :uid)");
		$q->bindValue(':naam', $name, PDO::PARAM_STR);
		$q->bindValue(':saldo', $saldo, PDO::PARAM_STR);
		if (!empty($uid)) {
			$q->bindValue(':uid', $uid, PDO::PARAM_STR);
		} else {
			$latest = $this->db->query("SELECT uid FROM CiviSaldo WHERE uid LIKE 'c%' ORDER BY uid DESC LIMIT 1")->fetchColumn();
			$q->bindValue(':uid', ++$latest, PDO::PARAM_STR);
		}

		return $q->execute();
	}

	public function updatePrice($productId, $price) {

		$this->db->beginTransaction();

		$q = $this->db->prepare("UPDATE CiviPrijs SET tot = CURRENT_TIMESTAMP WHERE product_id = :productId AND tot IS NULL ORDER BY van DESC LIMIT 1");
		$q->bindValue(':productId', $productId);
		$q->execute();

		$q = $this->db->prepare("INSERT INTO CiviPrijs (product_id, prijs) VALUES (:productId, :prijs)");
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

		$q = $this->db->prepare("UPDATE CiviProduct SET status = :visibility WHERE id = :productId");
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
		$value = array();
		foreach ($data as $key => $item) {

			$value[] = $key . ' = ' . $item;
		}
		$value = implode("\r\n", $value);

		$q = $this->db->prepare("INSERT INTO CiviLog (ip, type, data) VALUES(:ip, :type, :data)");
		$q->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
		$q->bindValue(':type', $type, PDO::PARAM_STR);
		$q->bindValue(':data', $value, PDO::PARAM_STR);
		$q->execute();
	}

}
