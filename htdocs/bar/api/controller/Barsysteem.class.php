<?php

require_once 'model/framework/Database.singleton.php';
require_once 'model/entity/groepen/LidStatus.enum.php';

class Barsysteem {
    var $db;
    private $admin = false;

    function __construct() {
        $this->db = Database::instance();
    }

    function isLoggedIn() {
        return isset($_COOKIE['barsysteem']) && md5('my_salt_is_strong' . $_COOKIE['barsysteem']) == '8f700ce34a77ef4ef9db9bbdde9e97d8';
    }

    function isAdmin() {
        if (!$this->admin)
            $this->admin = isset($_COOKIE['barsysteembeheer']) && md5('my_salt_is_strong' . $_COOKIE['barsysteembeheer']) == '5367b4668337c47a02cf87793a6a05d5';

        return $this->admin;
    }

    function getName($profiel) {

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

    function getAccount($uid) {
        $q = $this->db->prepare("SELECT stekUID, socCieId, naam, saldo, deleted, 1 as recent FROM socCieKlanten WHERE socCieId = :socCieId");
        $q->bindValue(":socCieId", $uid);
        $q->execute();
        $row = $q->fetch(PDO::FETCH_ASSOC);

        $account = array();
        $account["name"] = $row["naam"];
        $account["status"] = LidStatus::Nobody;
        if ($row["stekUID"]) {
            $profiel = ProfielModel::get($row["stekUID"]);
            if ($profiel) {
                $account["name"] = $this->getName($profiel);
                $account["status"] = $profiel->status;
            }
        }
        $account["uid"] = $row["socCieId"];
        $account["nickname"] = $row["naam"];
        $account["balance"] = $row["saldo"];
        $account["recent"] = $row["recent"];
        $account["deleted"] = $row["deleted"];
        return $account;
    }

    function getAccounts() {
        $terug = $this->db->query("SELECT socCieKlanten.stekUID, socCieKlanten.socCieId, socCieKlanten.naam, socCieKlanten.saldo, socCieKlanten.deleted, COUNT(socCieBestelling.totaal) AS recent FROM socCieKlanten LEFT JOIN socCieBestelling ON (socCieKlanten.socCieId = socCieBestelling.socCieId AND DATEDIFF(NOW(), tijd) < 100 AND socCieBestelling.deleted = 0) GROUP BY socCieKlanten.socCieId;");
        $result = array();
        foreach ($terug as $row) {
            $account = array();
            $account["name"] = $row["naam"];
            $account["status"] = LidStatus::Nobody;
            if ($row["stekUID"]) {
                $profiel = ProfielModel::get($row["stekUID"]);
                if ($profiel) {
                    $account["name"] = $this->getName($profiel);
                    $account["status"] = $profiel->status;
                }
            }
            $account["uid"] = $row["socCieId"];
            $account["nickname"] = $row["naam"];
            $account["balance"] = $row["saldo"];
            $account["recent"] = $row["recent"];
            $account["deleted"] = $row["deleted"];
            $result[] = $account;
        }
        return $result;
    }

    function getProducts() {
        $q = $this->db->prepare("SELECT id as productId, beheer as admin, prijs as price, beschrijving description, prioriteit as priority, status FROM socCieProduct AS P JOIN socCiePrijs AS R ON (P.id=R.productId AND (CURRENT_TIMESTAMP BETWEEN van AND tot)) ORDER BY prioriteit DESC");
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    function getLedgers() {

        $q = $this->db->prepare("SELECT id, type FROM socCieGrootboekType");
        $q->execute();
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    function processOrder($data) {
        $this->db->beginTransaction();

        $q = $this->db->prepare("INSERT INTO socCieBestelling (socCieId) VALUES (:socCieId);");
        $q->bindValue(":socCieId", $data->account->uid, PDO::PARAM_INT);
        $q->execute();
        $orderId = $this->db->lastInsertId();
        foreach ($data->orderItems as $productId => $amount) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES (:bestelId,  :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $amount, PDO::PARAM_INT);
            $q->bindValue(":bestelId", $orderId, PDO::PARAM_INT);
            $q->execute();
        }
        $total = $this->getOrderTotal($orderId);
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :totaal WHERE socCieId=:socCieId ;");
        $q->bindValue(":totaal", $total, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->account->uid, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling  SET totaal = :totaal WHERE id = :bestelId;");
        $q->bindValue(":totaal", $total, PDO::PARAM_INT);
        $q->bindValue(":bestelId", $orderId, PDO::PARAM_INT);
        $q->execute();

        if (!$this->db->commit()) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    function getOrdersOfAccount($uid) {
        $q = $this->db->prepare("SELECT *, B.deleted AS d, 0 AS oud FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId WHERE socCieId=:socCieId");
        $q->bindValue(":socCieId", $uid, PDO::PARAM_INT);
        $q->execute();
        return $this->processOrderResult($q->fetchAll(PDO::FETCH_ASSOC));
    }

    function getLatestOrders($account, $start, $end, $productType) {
        $productIDs = array();
        foreach ($productType as $product) {
            $productIDs[] = $product['value'];
        }

        if ($start == "") {
            $start = getDateTime(time() - 15 * 3600);
        } else {
            $start = $this->parseDate($start) . " 00:00:00";
        }
        if ($end == "") {
            $end = getDateTime();
        } else {
            $end = $this->parseDate($end) . " 23:59:59";
        }
        $qa = "";
        if ($account != "alles")
            $qa = "B.socCieId=:socCieId AND";
        $q = $this->db->prepare("SELECT *, B.deleted AS d, K.deleted AS oud FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId JOIN socCieKlanten AS K ON B.socCieId = K.socCieId WHERE " . $qa . " (tijd BETWEEN :begin AND :eind)");
        if ($account != "alles")
            $q->bindValue(":socCieId", $account, PDO::PARAM_INT);
        $q->bindValue(":begin", $start);
        $q->bindValue(":eind", $end);
        $q->execute();
        return $this->processOrderResult($q->fetchAll(PDO::FETCH_ASSOC), $productIDs);
    }

    function updateOrder($data) {

        $this->db->beginTransaction();

        // Add old order to saldo
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo + :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getOrderTotalTime($data->previousOrder->orderId, $data->previousOrder->time), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->account->uid, PDO::PARAM_INT);
        $q->execute();

        // Remove old contents of the order
        $q = $this->db->prepare("DELETE FROM socCieBestellingInhoud WHERE bestellingId = :bestelId");
        $q->bindValue(":bestelId", $data->previousOrder->orderId, PDO::PARAM_INT);
        $q->execute();

        // Add contents of the order
        foreach ($data->bestelLijst as $productId => $aantal) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES (:bestelId, :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":bestelId", $data->previousOrder->orderId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
            $q->execute();
        }

        // Substract new order from saldo
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getOrderTotalTime($data->previousOrder->orderId, $data->previousOrder->time), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->account->uid, PDO::PARAM_INT);
        $q->execute();

        // Update old order
        $q = $this->db->prepare("UPDATE socCieBestelling SET totaal = :totaal  WHERE id = :bestelId");
        $q->bindValue(":totaal", $this->getOrderTotalTime($data->previousOrder->orderId, $data->previousOrder->time), PDO::PARAM_INT);
        $q->bindValue(":bestelId", $data->previousOrder->orderId, PDO::PARAM_INT);
        $q->execute();

        // Roll back if error
        if (!$this->db->commit()) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    function getBalance($socCieId) {
        $q = $this->db->prepare("SELECT saldo FROM socCieKlanten WHERE socCieId = :socCieId");
        $q->bindValue(":socCieId", $socCieId);
        $q->execute();
        return $q->fetchColumn();
    }

    function deleteOrder($data) {
        $this->db->beginTransaction();
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo + :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $data->orderTotal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->accountId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling SET deleted = 1 WHERE id = :bestelId AND deleted = 0");
        $q->bindValue(":bestelId", $data->orderId, PDO::PARAM_INT);
        $q->execute();
        if (!$this->db->commit() || $q->rowCount() == 0) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    function undeleteOrder($data) {
        $this->db->beginTransaction();
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $data->orderTotal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->accountId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling SET deleted = 0 WHERE id = :bestelId AND deleted = 1");
        $q->bindValue(":bestelId", $data->orderId, PDO::PARAM_INT);
        $q->execute();
        if (!$this->db->commit() || $q->rowCount() == 0) {
            $this->db->rollBack();
            return false;
        }
        return true;
    }

    private function processOrderResult($queryResult, $productIDs = array()) {
        $result = array();
        foreach ($queryResult as $row) {
            if (!array_key_exists($row["bestellingId"], $result)) {
                $result[$row["bestellingId"]] = array();
                $result[$row["bestellingId"]]["orderItems"] = array();
                $result[$row["bestellingId"]]["orderTotal"] = $row["totaal"];
                $result[$row["bestellingId"]]["accountId"] = $row["socCieId"];
                $result[$row["bestellingId"]]["time"] = $row["tijd"];
                $result[$row["bestellingId"]]["orderId"] = $row["id"];
                $result[$row["bestellingId"]]["deleted"] = $row["d"];
                $result[$row["bestellingId"]]["old"] = $row["oud"];
            }
            $result[$row["bestellingId"]]["orderItems"][$row["productId"]] = 1 * $row["aantal"];
        }

        if (!empty($productIDs)) {
            foreach ($result as $key => $order) {

                $keep = false;
                foreach ($productIDs as $id) {
                    if (in_array($id, array_keys($order["orderItems"]))) {
                        $keep = true;
                    }
                }

                if (!$keep)
                    unset($result[$key]);
            }
        }

        return $result;
    }

    private function getOrderTotal($bestelId) {
        $q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM socCieBestellingInhoud AS I JOIN socCiePrijs AS P ON I . productId = P . productId WHERE bestellingId = :bestelId AND CURRENT_TIMESTAMP < tot AND CURRENT_TIMESTAMP > van");
        $q->bindValue(":bestelId", $bestelId, PDO::PARAM_INT);
        $q->execute();
        return $q->fetchColumn();
    }

    private function getOrderTotalTime($bestelId, $timestamp) {
        $q = $this->db->prepare("SELECT SUM(prijs * aantal) FROM socCieBestellingInhoud AS I JOIN socCiePrijs AS P ON I . productId = P . productId WHERE bestellingId = :bestelId AND :timeStamp < tot AND :timeStamp > van");
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
    public function getLedgerInput() {

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

    public function getToolData() {

        $data = array();

        $data['sum_balances'] = $this->sumBalances();
        $data['sum_balances_people'] = $this->sumBalances(true);
        $data['red'] = $this->getRed();

        return $data;
    }

    private function sumBalances($peopleOnly = false) {

        $after = $peopleOnly ? "AND stekUID IS NOT NULL" : "";

        return $this->db->query("SELECT SUM(saldo) AS sum FROM socCieKlanten WHERE deleted = 0 " . $after)->fetch(PDO::FETCH_ASSOC);
    }

    private function getRed() {

        $result = array();

        $q = $this->db->query("SELECT stekUID, saldo FROM socCieKlanten WHERE deleted = 0 AND saldo < 0 AND stekUID IS NOT NULL ORDER BY saldo");
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {

            $result[] = array(
                'name' => $this->getName(ProfielModel::get($r['stekUID'])),
                'email' => ProfielModel::get($r['stekUID'])->getPrimaryEmail(),
                'balance' => $r['saldo'],
                'status' => ProfielModel::get($r['stekUID'])->status
            );
        }

        return $result;
    }

    public function addProduct($name, $price, $type) {

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

    public function updateAccount($uid, $name) {

        $q = $this->db->prepare("UPDATE socCieKlanten SET naam = :naam WHERE socCieId = :id");
        $q->bindValue(':id', $uid, PDO::PARAM_INT);
        $q->bindValue(':naam', $name, PDO::PARAM_STR);
        return $q->execute();
    }

    public function removeAccount($uid) {

        $q = $this->db->prepare("UPDATE socCieKlanten SET deleted = 1 WHERE socCieId = :id AND saldo = 0");
        $q->bindValue(':id', $uid, PDO::PARAM_INT);
        $q->execute();
        return $q->rowCount();
    }

    public function addAccount($name, $saldo, $profileUID) {
        $q = $this->db->prepare("INSERT INTO socCieKlanten (naam, saldo, stekUID) VALUES (:naam, :saldo, :stekUID)");
        $q->bindValue(':naam', $name, PDO::PARAM_STR);
        $q->bindValue(':saldo', $saldo, PDO::PARAM_STR);
        if (!empty($profileUID))
            $q->bindValue(':stekUID', $profileUID, PDO::PARAM_STR);
        else
            $q->bindValue(':stekUID', null, PDO::PARAM_INT);

        return $q->execute();
    }

    public function updatePrice($productId, $price) {

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

    public function updateVisibility($productId, $visibility) {

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
    public function log($type, $data) {
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
