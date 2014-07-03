<?php

require_once 'MVC/model/DatabaseAdmin.singleton.php';


class Barsysteem
{

    var $db;

    function Barsysteem()
    {
        $this->db = Database::instance();
    }

    function getPersonen()
    {
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
        $terug = $this->db->query("SELECT * FROM socCieProduct WHERE status = '1' ORDER BY prioriteit DESC");
        $result = array();
        foreach ($terug as $row) {
            $product = array();
            $product["productId"] = $row["id"];
            $product["prijs"] = $row["prijs"];
            $product["btw"] = $row["btw"];
            $product["beschrijving"] = $row["beschrijving"];
            $product["prioriteit"] = $row["prioriteit"];
            $product["alcohol"]=$row["alcohol"];
            $result[$row["id"]] = $product;
        }
        return $result;
    }

    function verwerkBestelling($data)
    {
        $this->db->beginTransaction();
        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("INSERT INTO socCieBestelling (socCieId, totaal) VALUES ( :socCieId, :bestelTotaal);");
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
        $q->execute();
        foreach ($data->bestelLijst as $productId => $aantal) {
            $q = $this->db->prepare("INSERT INTO socCieBestellingInhoud VALUES ((SELECT MAX(id) FROM socCieBestelling),  :productId, :aantal);");
            $q->bindValue(":productId", $productId, PDO::PARAM_INT);
            $q->bindValue(":aantal", $aantal, PDO::PARAM_INT);
            $q->execute();
        }
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

    function getBestellingLaatste($aantal)
    {
        $q = $this->db->prepare("SELECT * FROM (SELECT * FROM socCieBestelling ORDER BY tijd DESC LIMIT 0,:aantal) AS B JOIN socCieBestellingInhoud AS I ON B.id=I.bestellingId");
        $q->bindValue(":aantal", intval($aantal), PDO::PARAM_INT);
        $q->execute();
        return $this->verwerkBestellingResultaat($q->fetchAll(PDO::FETCH_ASSOC));
    }

    function updateBestelling($data)
    {
        $this->db->beginTransaction();

        $q = $this->db->prepare("UPDATE socCieKlanten SET saldo = saldo - :bestelTotaal WHERE socCieId=:socCieId;");
        $q->bindValue(":bestelTotaal", $data->bestelTotaal - $data->oudeBestelling->bestelTotaal, PDO::PARAM_INT);
        $q->bindValue(":socCieId", $data->persoon->socCieId, PDO::PARAM_INT);
        $q->execute();
        $q = $this->db->prepare("UPDATE socCieBestelling SET totaal = :bestelTotaal WHERE id = :bestelId");
        $q->bindValue(":bestelId", $data->oudeBestelling->bestelId, PDO::PARAM_INT);
        $q->bindValue(":bestelTotaal", $data->bestelTotaal, PDO::PARAM_INT);
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
        return $q->fetchAll()[0]["saldo"];
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
            if (!$result[$row["bestellingId"]]) {
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

}

?>
