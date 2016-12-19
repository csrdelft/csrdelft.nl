<?php

class SaldoModel extends PersistenceModel {
    const ORM = 'Saldo';
    const DIR = 'fiscaal/';

    protected static $instance;

    public function getDataPointsForMaalCie($uid, $timespan) {
        if (!$this->magGrafiekZien($uid, "maalcie")) {
            return null;
        }

        $points = $this->find('uid = ? AND cie = "maalcie" AND moment > (NOW() - INTERVAL ' . $timespan . ' DAY)', array($uid))->fetchAll();

        if (!empty($points)) {
            //herhaal laatste datapunt om grafiek te tekenen tot aan vandaag
            $row = clone end($points);
            $row->moment = getDateTime();
            array_push($points, $row);
        } else {
            // haal de het meest recente saldo op
            $points[] = $this->find('uid = ? AND cie = "maalcie"', array($uid), null, 'moment', 1)->fetch();
            if (isset($points[0]->moment)) {
                $points[0]->moment = getDateTime();
            }
        }

        if (!empty($points)) {
            // herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
            $row = reset($points);
            $time = strtotime('-' . $timespan . ' days');
            $saldo = new Saldo();
            $saldo->saldo = $row->saldo;
            $saldo->moment = getDateTime($time + 3600);
            array_unshift($points, $saldo);
        }

        return array(
            "label" => "MaalCie",
            "data" => $points,
            "threshold" => array("below" => 0, "color" => "red"),
            "lines" => array("steps" => true)
        );
    }

    public function getDataPointsForSocCie($uid, $timespan) {
        if (!$this->magGrafiekZien($uid, "soccie")) {
            return null;
        }
        $model = DynamicEntityModel::makeModel('socCieKlanten');
        $klant = $model->findSparse(array('socCieId', 'saldo'), 'stekUID = ?', array($uid), null, null, 1)->fetch();
        if (!$klant) {
            return null;
        }

        $saldo = $klant->saldo;
        $data = array(array('moment' => getDateTime(), 'saldo' => round($saldo / 100, 2)));
        $model = DynamicEntityModel::makeModel('socCieBestelling');
        $bestellingen = $model->findSparse(array('tijd', 'totaal'), 'socCieId = ? AND deleted = FALSE AND tijd>(NOW() - INTERVAL ? DAY)', array($klant->socCieId, $timespan), null, 'tijd DESC');
        foreach ($bestellingen as $bestelling) {
            $data[] = array('moment' => $bestelling->tijd, 'saldo' => round($saldo / 100, 2));
            $saldo += $bestelling->totaal;
        }

        if (!empty($data)) {
            // herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
            // Pas op, soccie is omgedraaid van maalcie omdat saldo's op runtime berekend worden
            $row = end($data);
            $time = strtotime('-' . $timespan . ' days');
            array_push($data, array('moment' => getDateTime($time + 3600), 'saldo' => $row['saldo']));
        }

        $points = array();
        foreach ($data as $entry) {
            $saldo = new Saldo();
            $saldo->moment = $entry['moment'];
            $saldo->saldo = $entry['saldo'];
            $points[] = $saldo;
        }

        return array(
            "label" => "SocCie",
            "data" => $points,
            "threshold" => array("below" => 0, "color" => "red"),
            "lines" => array("steps" => true)
        );
    }

    public function getDataPoints($uid, $timespan) {
        // array_filter haalt grafieken die niet gezien mogen worden eruit.
        return json_encode(array_filter(array(
            $this->getDataPointsForMaalCie($uid, $timespan),
            $this->getDataPointsForSocCie($uid, $timespan)
        )));
    }

    public function magGrafiekZien($uid, $cie = null) {
        //mogen we uberhaupt een grafiek zien?
        if ($cie === null) {
            return LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,commissie:SocCie,commissie:MaalCie');
        }
        if (LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,commissie:' . $cie)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $key
     * @deprecated
     */
    public static function putMaalcieCsv($key = 'CSVSaldi') {
        $db = MijnSqli::instance();
        if (is_array($_FILES) AND isset($_FILES[$key])) {
            //bestandje uploaden en verwerken...
            $bCorrect = true;
            //niet met csv functies omdat dat misging met OS-X regeleinden...
            $aRegels = preg_split("/[\s]+/", file_get_contents($_FILES['CSVSaldi']['tmp_name']));
            $row = 0;
            foreach ($aRegels as $regel) {
                $regel = str_replace(array('"', ' ', "\n", "\r"), '', $regel);
                $aRegel = explode(',', $regel);
                if (array_key_exists(0, $aRegel) AND array_key_exists(1, $aRegel) AND
                    AccountModel::isValidUid($aRegel[0]) AND is_numeric($aRegel[1])
                ) {
                    $sQuery = "
						UPDATE profielen
						SET maalcieSaldo=" . $aRegel[1] . "
						WHERE uid='" . $aRegel[0] . "'
						LIMIT 1;";
                    if ($db->query($sQuery)) {
                        //nu ook nog even naar het saldolog schrijven
                        $logQuery = "
							INSERT INTO saldolog (
								uid, moment, cie, saldo
							)VALUES(
								'" . $aRegel[0] . "',
								'" . getDateTime() . "',
								'maalcie',
								" . $aRegel[1] . "
							);";
                        $db->query($logQuery);
                    } else {
                        $bCorrect = false;
                    }
                    $row++;
                }
            }
            CsrMemcache::instance()->flush();
            if ($bCorrect === true) {
                setMelding('Er zijn ' . $row . ' regels ingevoerd. Als dit er minder zijn dan u verwacht zitten er ongeldige regels in uw bestand.', 0);
            } else {
                setMelding('Helaas, er ging iets mis. Controleer uw bestand! mysql gaf terug <' . $db->error() . '>', -1);
            }
        }
    }
}
