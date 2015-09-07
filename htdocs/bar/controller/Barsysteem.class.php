<?php

require_once 'model/framework/Database.singleton.php';

class Barsysteem
{

    var $db;
    private $beheer;

    function Barsysteem()
    {
        $this->db = Database::instance();
    }

    function isLoggedIn()
    {
        return isset($_COOKIE['barsysteem']) && md5('my_salt_is_strong' . $_COOKIE['barsysteem']) == '8f700ce34a77ef4ef9db9bbdde9e97d8';
    }

    function isBeheer()
    {
        if (!$this->beheer)
            $this->beheer = isset($_COOKIE['barsysteembeheer']) && md5('my_salt_is_strong' . $_COOKIE['barsysteembeheer']) == '5367b4668337c47a02cf87793a6a05d5';

        return $this->beheer;
    }

    function getNaam($profiel)
    {

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

    function getPersonen()
    {
        require_once 'model/entity/groepen/LidStatus.enum.php';
        $terug = $this->db->query("SELECT socCieKlanten.stekUID, socCieKlanten.socCieId, socCieKlanten.naam, socCieKlanten.saldo, socCieKlanten.deleted, COUNT(socCieBestelling.totaal) AS recent FROM socCieKlanten LEFT JOIN socCieBestelling ON (socCieKlanten.socCieId = socCieBestelling.socCieId AND DATEDIFF(NOW(), tijd) < 100 AND socCieBestelling.deleted = 0) GROUP BY socCieKlanten.socCieId;");
        $result = array();
        foreach ($terug as $row) {
            $persoon = array();
            $persoon["naam"] = $row["naam"];
            $persoon["status"] = LidStatus::Nobody;
            if ($row["stekUID"]) {
                $profiel = ProfielModel::get($row["stekUID"]);
                if ($profiel) {
                    $persoon["naam"] = $this->getNaam($profiel);
                    $persoon["status"] = $profiel->status;
                }
            }
            $persoon["socCieId"] = $row["socCieId"];
            $persoon["bijnaam"] = $row["naam"];
            $persoon["saldo"] = $row["saldo"];
            $persoon["recent"] = $row["recent"];
            $persoon["deleted"] = $row["deleted"];
            $result[$row["socCieId"]] = $persoon;
        }
        return $result;
    }

    function getProducten()
    {
        $q = $this->db->prepare("SELECT id, beheer, prijs, beschrijving, prioriteit, status FROM socCieProduct AS P JOIN socCiePrijs AS R ON (P.id=R.productId AND (CURRENT_TIMESTAMP BETWEEN van AND tot)) ORDER BY prioriteit DESC");
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
            $result[$row["id"]] = $product;
        }
        return $result;
    }

    function getGrootboeken()
    {

        $q = $this->db->prepare("SELECT id, type FROM socCieGrootboekType");
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    function verwerkBestelling($data)
    {
        $this->db->beginTransaction();

        $q = $this->db->prepare("INSERT INTO socCieBestelling (socCieId) VALUES (:socCieId);");
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $bestelId = $this->db->lastInsertId();
        foreach ($data->bestelLijst as $productId => $aantal) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES (:bestelId,  :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
            $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
            $q->execute();
        }
        $totaal = $this->getBestellingTotaal($bestelId);
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :totaal WHERE socCieId=:socCieId ;");
        $q->bindValue(":totaal", $totaal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling  SET totaal = :totaal WHERE id = :bestelId;");
        $q->bindValue(":totaal", $totaal, PDO::PARAM_INT);
        $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
        $q->execute();

        if (!$this->db->commit()) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    function getBestellingPersoon($socCieId)
    {
        $q = $this->db->prepare("SELECT *, B.deleted AS d, 0 AS oud FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId WHERE socCieId=:socCieId");
        $q->bindValue(":socCieId", $socCieId, PDO::PARAM_INT);
        $q->execute();
        return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC));
    }

    function getBestellingLaatste($persoon, $begin, $eind, $productType)
    {
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
            $qa = "B.socCieId=:socCieId AND";
        $q = $this->db->prepare("SELECT *, B.deleted AS d, K.deleted AS oud FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId JOIN socCieKlanten AS K ON B.socCieId = K.socCieId WHERE " . $qa . " (tijd BETWEEN :begin AND :eind)");
        if ($persoon != "alles")
            $q->bindValue(":socCieId", $persoon, PDO::PARAM_INT);
        $q->bindValue(":begin", $begin);
        $q->bindValue(":eind", $eind);
        $q->execute();
        return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC), $productIDs);
    }

    function updateBestelling($data)
    {

        $this->db->beginTransaction();

        // Add old order to saldo
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo + :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();

        // Remove old contents of the order
        $q = $this->db->prepare("DELETE FROM socCieBestellingInhoud WHERE bestellingId = :bestelId");
        $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
        $q->execute();

        // Add contents of the order
        foreach ($data->bestelLijst as $productId => $aantal) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES (:bestelId, :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
            $q->execute();
        }

        // Substract new order from saldo
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();

        // Update old order
        $q = $this->db->prepare("UPDATE socCieBestelling SET totaal = :totaal  WHERE id = :bestelId");
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

    function getSaldo($socCieId)
    {
        $q = $this->db->prepare("SELECT saldo FROM socCieKlanten WHERE socCieId = :socCieId");
        $q->bindValue(":socCieId", $socCieId);
        $q->execute();
        return $q->fetchColumn();
    }

    function verwijderBestelling($data)
    {
        $this->db->beginTransaction();
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo + :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling SET deleted = 1 WHERE id = :bestelId AND deleted = 0");
        $q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
        $q->execute();
        if (!$this->db->commit() || $q->rowCount() == 0) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    function undoVerwijderBestelling($data)
    {
        $this->db->beginTransaction();
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling SET deleted = 0 WHERE id = :bestelId AND deleted = 1");
        $q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
        $q->execute();
        if (!$this->db->commit() || $q->rowCount() == 0) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    private function verwerkBestellingResultaat($queryResult, $productIDs = array())
    {
        $result = array();
        foreach ($queryResult as $row) {
            if (!array_key_exists($row["bestellingId"], $result)) {
                $result[$row["bestellingId"]] = array();
                $result[$row["bestellingId"]]["bestelLijst"] = array();
                $result[$row["bestellingId"]]["bestelTotaal"] = $row["totaal"];
                $result[$row["bestellingId"]]["persoon"] = $row["socCieId"];
                $result[$row["bestellingId"]]["tijd"] = $row["tijd"];
                $result[$row["bestellingId"]]["bestelId"] = $row["id"];
                $result[$row["bestellingId"]]["deleted"] = $row["d"];
                $result[$row["bestellingId"]]["oud"] = $row["oud"];
            }
            $result[$row["bestellingId"]]["bestelLijst"][$row["productId"]] = 1 * $row["aantal"];
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

    private function getBestellingTotaal($bestelId)
    {
        $q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM socCieBestellingInhoud AS I JOIN socCiePrijs AS P ON I . productId = P . productId WHERE bestellingId = :bestelId AND CURRENT_TIMESTAMP < tot AND CURRENT_TIMESTAMP > van");
        $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
        $q->execute();
        return $q->fetchColumn();
    }

    private function getBestellingTotaalTijd($bestelId, $timestamp)
    {
        $q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM socCieBestellingInhoud AS I JOIN socCiePrijs AS P ON I . productId = P . productId WHERE bestellingId = :bestelId AND :timeStamp < tot AND :timeStamp > van");
        $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
        $q->bindValue(":timeStamp", $timestamp, PDO::PARAM_STMT);
        $q->execute();
        return $q->fetchColumn();
    }

    private function parseDate($date)
    {
        $elementen = explode(" ", $date);
        $datum = str_pad($elementen[0], 2, "0", STR_PAD_LEFT);
        $maanden = ["Januari" => "01", "Februari" => "02", "Maart" => "03", "April" => "04", "Mei" => "05", "Juni" => "06", "Juli" => "07", "Augustus" => "08", "September" => "09", "Oktober" => "10", "November" => "11", "December" => "12"];
        return ($elementen[2] . "-" . $maanden[$elementen[1]] . "-" . $datum);
    }

    // Beheer
    public function getGrootboekInvoer()
    {

        // GROUP BY week
        $q = $this->db->prepare("
SELECT G.type,
	SUM(I.aantal * PR.prijs) AS total,
	WEEK(B.tijd, 3) AS week,
	YEARWEEK(B.tijd, 3) AS yearweek
FROM socCieBestelling AS B
JOIN socCieBestellingInhoud AS I ON
	B.id = I.bestellingId
JOIN socCieProduct AS P ON
	I.productId = P.id
JOIN socCiePrijs AS PR ON
	P.id = PR.productId
	AND (B.tijd BETWEEN PR.van AND PR.tot)
JOIN socCieGrootboekType AS G ON
	P.grootboekId = G.id
WHERE
	B.deleted = 0 AND
	G.status = 1
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

    public function getToolData()
    {

        $data = array();

        $data['sum_saldi'] = $this->sumSaldi();
        $data['sum_saldi_lid'] = $this->sumSaldi(true);
        $data['red'] = $this->getRed();

        return $data;
    }

    private function sumSaldi($profielOnly = false)
    {

        $after = $profielOnly ? "AND stekUID IS NOT NULL" : "";

        return $this->db->query("SELECT SUM(saldo) AS sum FROM socCieKlanten WHERE deleted = 0 " . $after)->fetch(PDO::FETCH_ASSOC);
    }

    private function getRed()
    {

        $result = array();

        $q = $this->db->query("SELECT stekUID, saldo FROM socCieKlanten WHERE deleted = 0 AND saldo < 0 AND stekUID IS NOT NULL ORDER BY saldo");
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {

            $result[] = array(
                'naam' => $this->getNaam(ProfielModel::get($r['stekUID'])),
                'email' => ProfielModel::get($r['stekUID'])->getPrimaryEmail(),
                'saldo' => $r['saldo'],
                'status' => ProfielModel::get($r['stekUID'])->status
            );
        }

        return $result;
    }

    public function addProduct($name, $price, $type)
    {

        if ($type < 1)
            return false;

        $this->db->beginTransaction();

        $q = $this->db->prepare("INSERT INTO socCieProduct(status, beschrijving, prioriteit, grootboekId, beheer) VALUES(1, :name, -5000, :type, 0)");
        $q->bindValue(':name', $name);
        $q->bindValue(':type', $type);
        $q->execute();

        $q = $this->db->prepare("INSERT INTO socCiePrijs(productId, prijs) VALUES(:productId, :price)");
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

        $q = $this->db->prepare("UPDATE socCieKlanten SET naam = :naam WHERE socCieId = :id");
        $q->bindValue(':id', $id, PDO::PARAM_INT);
        $q->bindValue(':naam', $name, PDO::PARAM_STR);
        return $q->execute();
    }

    public function removePerson($id)
    {

        $q = $this->db->prepare("UPDATE socCieKlanten SET deleted = 1 WHERE socCieId = :id AND saldo = 0");
        $q->bindValue(':id', $id, PDO::PARAM_INT);
        $q->execute();
        return $q->rowCount();
    }

    public function addPerson($name, $saldo, $uid)
    {

        $q = $this->db->prepare("INSERT INTO socCieKlanten (naam, saldo, stekUID) VALUES (:naam, :saldo, :stekUID)");
        $q->bindValue(':naam', $name, PDO::PARAM_STR);
        $q->bindValue(':saldo', $saldo, PDO::PARAM_STR);
        if (!empty($uid))
            $q->bindValue(':stekUID', $uid, PDO::PARAM_STR);
        else
            $q->bindValue(':stekUID', null, PDO::PARAM_INT);

        return $q->execute();
    }

    public function updatePrice($productId, $price)
    {

        $this->db->beginTransaction();

        $q = $this->db->prepare("UPDATE socCiePrijs SET tot = CURRENT_TIMESTAMP WHERE productId = :productId ORDER BY tot DESC LIMIT 1");
        $q->bindValue(':productId', $productId);
        $q->execute();

        $q = $this->db->prepare("INSERT INTO socCiePrijs (productId, prijs) VALUES (:productId, :prijs)");
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

        $q = $this->db->prepare("UPDATE socCieProduct SET status = :visibility WHERE id = :productId");
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
        $value = array();
        foreach ($data as $key => $item) {

            $value[] = $key . ' = ' . $item;
        }
        $value = implode("\r\n", $value);

        $q = $this->db->prepare("INSERT INTO socCieLog (ip, type, value) VALUES(:ip, :type, :value)");
        $q->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $q->bindValue(':type', $type, PDO::PARAM_STR);
        $q->bindValue(':value', $value, PDO::PARAM_STR);
        $q->execute();
    }

}
