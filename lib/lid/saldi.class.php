<?php

/**
 * saldi.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 */
class Saldi {

	private $uid;
	public $cie;
	private $data;

	public function __construct($uid, $cie = 'soccie', $timespan = 11) {
		$this->uid = $uid;
		$this->cie = $cie;
		$this->load((int) $timespan);
	}

	private function load($timespan) {
		if ($this->uid == '0000') {
			$sQuery = "
				SELECT LEFT(moment, 16) AS moment, SUM(saldo) AS saldo
				FROM saldolog
				WHERE cie='" . $this->cie . "'
				  AND moment>(NOW() - INTERVAL " . $timespan . " DAY)
				GROUP BY LEFT(moment, 16);";
		} else {
			$sQuery = "
				SELECT moment, saldo
				FROM saldolog
				WHERE uid='" . $this->uid . "'
				  AND cie='" . $this->cie . "'
				  AND moment>(NOW() - INTERVAL " . $timespan . " DAY);";
		}
		$this->data = MijnSqli::instance()->query2array($sQuery);
		if (!is_array($this->data)) {
			$this->data = array();
		}
		// fetch additional data from soccie system
		if ($this->cie == 'soccie') {
			$model = DynamicEntityModel::makeModel('socCieKlanten');
			$klant = $model->findSparse(array('socCieId', 'saldo'), 'stekUID = ?', array($this->uid), null, null, 1)->fetch();
			if ($klant) {
				$saldo = $klant->saldo;
				$data = array(array('moment' => getDateTime(), 'saldo' => round($saldo / 100, 2)));
				$model = DynamicEntityModel::makeModel('socCieBestelling');
				$bestellingen = $model->findSparse(array('tijd', 'totaal'), 'socCieId = ? AND deleted = FALSE AND tijd>(NOW() - INTERVAL ? DAY)', array($klant->socCieId, $timespan), null, 'tijd DESC');
				foreach ($bestellingen as $bestelling) {
					$data[] = array('moment' => $bestelling->tijd, 'saldo' => round($saldo / 100, 2));
					$saldo += $bestelling->totaal;
				}
				$this->data = array_merge($this->data, array_reverse($data));
			}
		} elseif ($this->cie == 'maalcie') {
			if (empty($this->data)) {
				if ($this->uid != '0000') {
					$sQuery = "
					SELECT moment, saldo
					FROM saldolog
					WHERE uid='" . $this->uid . "'
					  AND cie='" . $this->cie . "'
					ORDER BY moment DESC
					LIMIT 1;";
					$this->data = MijnSqli::instance()->query2array($sQuery);
					if (!is_array($this->data)) {
						$this->data = array();
					}
					if (isset($this->data[0]['moment'])) {
						$this->data[0]['moment'] = getDateTime();
					}
				}
			} else {
				//herhaal laatste datapunt om grafiek te tekenen tot aan vandaag
				$row = end($this->data);
				array_push($this->data, array('moment' => getDateTime(), 'saldo' => $row['saldo']));
			}
		}
		if (!empty($this->data)) {
			// herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
			$row = reset($this->data);
			$time = strtotime('-' . $timespan . ' days');
			array_unshift($this->data, array('moment' => getDateTime($time + 3600), 'saldo' => $row['saldo']));
		}
	}

	public function getNaam() {
		switch ($this->cie) {
			case 'maalcie': return 'MaalCie';
				break;
			case 'soccie': return 'SocCie';
				break;
		}
	}

	public function getData() {
		return $this->data;
	}

	public function getValues() {
		foreach ($this->data as $row) {
			$return[] = $row['saldo'];
		}
		return $return;
	}

	public function getKeys() {
		foreach ($this->data as $row) {
			$return[] = str_replace(array('-', ':', ' '), '', $row['moment']);
		}
		return $return;
	}

	public static function magGrafiekZien($uid, $cie = null) {
		//mogen we uberhaupt een grafiek zien?
		if ($cie === null) {
			return LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,groep:soccie,groep:maalcie');
		}
		if (LoginModel::getUid() === $uid OR LoginModel::mag('P_LEDEN_MOD,groep:' . $cie)) {
			return true;
		}
		return false;
	}

	/**
	 * Geef wat javascriptcode terug met data-series defenities voor Flot
	 */
	public static function getDatapoints($uid, $timespan) {
		$aSaldi = array();
		try {
			$aSaldi['maalcie'] = new Saldi($uid, 'maalcie', $timespan);
			$aSaldi['soccie'] = new Saldi($uid, 'soccie', $timespan);
		} catch (Exception $e) {
			if (!startsWith($e->getMessage(), 'Saldi::load() gefaald.')) {
				setMelding($e->getMessage(), -1);
			}
		}
		$series = array();
		foreach ($aSaldi as $saldi) {
			if (!Saldi::magGrafiekZien($uid, $saldi->cie)) {
				//deze slaan we over, die mogen we niet zien kennelijk
				continue;
			}
			$points = array();
			foreach ($saldi->getData() as $data) {
				$p = '[';
				$p .= strtotime($data['moment']) * 1000;
				$p .= ', ';
				$p .= sprintf('%.2F', $data['saldo']);
				$p .= "]";
				$points[] = $p;
			}
			$series[] = '{
	"label": "' . $saldi->getNaam() . '", 
	"data": [ ' . implode(", ", $points) . ' ],
	"threshold": { "below": 0, "color": "red" },
	"lines": { "steps": true }
}';
		}
		return '[' . implode(', ', $series) . ']';
	}

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
						AccountModel::isValidUid($aRegel[0]) AND is_numeric($aRegel[1])) {
					$sQuery = "
						UPDATE lid
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
