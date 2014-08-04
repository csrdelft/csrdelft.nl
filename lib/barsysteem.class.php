<?php

require_once 'MVC/model/Database.singleton.php';


class Barsysteem
{

    var $db;

    function Barsysteem()
    {
        $this->db = Database::instance();
    }
	
	function isLoggedIn()
	{
		return isset($_COOKIE['barsysteem']) && md5('my_salt_is_strong' . $_COOKIE['barsysteem']) == '8f700ce34a77ef4ef9db9bbdde9e97d8';
	}

    function getPersonen()
    {
		// SELECT *FROM socCieKlanten LEFT JOIN socCieBestelling ON socCieKlanten.socCieId = socCieBestelling.socCieId AND DATEDIFF(NOW(), tijd) < 100 GROUP BY socCieKlanten.socCieId ORDER BY totaal DESC;
        $terug = $this->db->query("SELECT * FROM socCieKlanten;");
        $result = array();
        foreach ($terug as $row) {
            $persoon = array();
            $persoon["naam"] = $row["naam"];
            if ($row["stekUID"]) {
                $lid = LidCache::getLid($row["stekUID"]);
                $persoon["naam"] = $lid->getNaam();
            }
            $persoon["socCieId"] = $row["socCieId"];
            $persoon["bijnaam"] = $row["naam"];
            $persoon["saldo"] = $row["saldo"];
            $result[$row["socCieId"]] = $persoon;
        }
        return $result;
    }

    function getProducten()
    {
        $terug = $this->db->query("SELECT id, prijs, btw, beschrijving, prioriteit, alcohol FROM socCieProduct as P JOIN socCiePrijs as R ON P.id=R.productId WHERE status = '1' AND CURRENT_TIMESTAMP<tot AND CURRENT_TIMESTAMP>van ORDER BY prioriteit DESC");
        $result = array();
        foreach ($terug as $row) {
            $product = array();
            $product["productId"] = $row["id"];
            $product["prijs"] = $row["prijs"];
            $product["btw"] = $row["btw"];
            $product["beschrijving"] = $row["beschrijving"];
            $product["prioriteit"] = $row["prioriteit"];
            $product["alcohol"] = $row["alcohol"];
            $result[$row["id"]] = $product;
        }
        return $result;
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
        $q = $this->db->prepare("SELECT * FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId WHERE socCieId=:socCieId");
        $q->bindValue(":socCieId", $socCieId, PDO::PARAM_INT);
        $q->execute();
        return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC));
    }

    function getBestellingLaatste($persoon, $begin, $eind)
    {
        if ($begin == "") {
            $begin = date("Y-m-d H:i:s", time() - 15 * 3600);
        } else {
            $begin = $this->parseDate($begin) . " 00:00:00";
        }
        if ($eind == "") {
            $eind = date("Y-m-d H:i:s");
        } else {
            $eind = $this->parseDate($eind) . " 23:59:59";
        }
        $qa = "";
        if ($persoon != "alles") $qa = "socCieId=:socCieId AND";
        $q = $this->db->prepare("SELECT * FROM socCieBestelling AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId WHERE " . $qa . " tijd>=:begin AND tijd<=:eind");
        if ($persoon != "alles") $q->bindValue(":socCieId", $persoon, PDO::PARAM_INT);
        $q->bindValue(":begin", $begin);
        $q->bindValue(":eind", $eind);
        $q->execute();
        return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC));
    }

    function updateBestelling($data)
    {
        $this->db->beginTransaction();

        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("DELETE FROM socCieBestellingInhoud  WHERE bestellingId = :bestelId");
        $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
        $q->execute();
        foreach ($data->bestelLijst as $productId => $aantal) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES (:bestelId, :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
            $q->execute();
        }
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo + :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("INSERT INTO socCieBestelling (totaal) VALUES (:totaal);");
        $q->bindValue(":totaal", $this->getBestellingTotaalTijd($data->oudeBestelling->bestelId, $data->oudeBestelling->tijd), PDO::PARAM_INT);
        $q->execute();
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
        $q = $this->db->prepare("DELETE FROM socCieBestellingInhoud  WHERE bestellingId = :bestelId");
        $q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("DELETE FROM socCieBestelling WHERE id = :bestelId");
        $q->bindValue(":bestelId", $data->bestelId, PDO::PARAM_INT);
        $q->execute();
        if (!$this->db->commit()) {
            $this->db->rollBack();
            return false;
        }
        return true;

    }

    private function verwerkBestellingResultaat($queryResult)
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

            }
            $result[$row["bestellingId"]]["bestelLijst"][$row["productId"]] = 1 * $row["aantal"];
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
	public function getGrootboekInvoer() {
	
		$weeks = array();
	
		for($i = 0; $i < 52; $i++) {
		
			$week = array();
			
			$week['title'] = 'Week ' . $i;
			
			$weeks[] = $week;
		
		}
		
		return $weeks;
	
	}
	
	// Log action by type
	public function log($type, $data)
	{
		$value = array();
		foreach($data as $key => $item) {
		
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
