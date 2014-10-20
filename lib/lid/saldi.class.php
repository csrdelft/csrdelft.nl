<?php

/**
 * saldi.class.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 */
class Saldi {

	private $uid;
	public $cie;
	private $data;

	public function __construct($uid, $cie = 'soccie', $timespan = 40) {
		$this->uid = $uid;
		$this->cie = $cie;
		$this->load((int) $timespan);
	}

	private function load($timespan) {
		$timespan = (int) $timespan;
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
			throw new Exception('Saldi::load() gefaald.' . $sQuery);
		}

		setMelding($this->cie, 0); //DEBUG

		// fetch new data from soccie system
		$now = time();
		$date_back = strtotime('-' . $timespan . ' days', $now);
		if ($this->cie == 'soccie') {
			$model = DynamicEntityModel::makeModel('socCieKlanten');
			$klant = $model->find('stekUID = ?', array($this->uid), null, null, 1)->fetch();
			$saldo = $klant->saldo;
			if ($klant) {
				$data = array(array('moment' => getDateTime($now), 'saldo' => round($saldo / 100, 2)));
				$model = DynamicEntityModel::makeModel('socCieBestelling');
				$bestellingen = $model->find('socCieId = ? AND deleted = FALSE AND tijd > ?', array($klant->socCieId, $date_back), 'tijd DESC');
				foreach ($bestellingen as $bestelling) {
					$saldo += $bestelling->totaal;
					$data[] = array('moment' => $bestelling->tijd, 'saldo' => round($saldo / 100, 2));
				}
				$this->data = array_merge($this->data, array_reverse($data));
			}
		}
		// herhaal eerste datapunt om grafiek te tekenen vanaf begin timespan
		$row = reset($this->data);
		array_unshift($this->data, array('moment' => getDateTime($date_back + 3600), 'saldo' => $row['saldo']));
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
		$saldi = array();
		try {
			$saldi['maalcie'] = new Saldi($uid, 'maalcie', $timespan);
			$saldi['soccie'] = new Saldi($uid, 'soccie', $timespan);
		} catch (Exception $e) {
			if (!startsWith($e->getMessage(), 'Saldi::load() gefaald.')) {
				setMelding($e->getMessage(), -1);
			}
		}
		$series = array();
		foreach ($saldi as $cie) {
			if (!Saldi::magGrafiekZien($uid, $cie->cie)) {
				//deze slaan we over, die mogen we niet zien kennelijk
				continue;
			}
			$points = array();
			foreach ($cie->getData() as $data) {
				$p = '[';
				$p .= strtotime($data['moment']);
				$p .= ', ';
				$p .= sprintf('%.2F', $data['saldo']);
				$p .= "]";
				$points[] = $p;
			}
			$series[] = '{
	"label": "' . $cie->getNaam() . '", 
	"data": [ ' . implode(", ", $points) . ' ],
	"threshold": { "below": 0, "color": "red" },
	"lines": { "steps": true }
}';
		}
		return '[' . implode(', ', $series) . ']';
	}

	public static function putSoccieXML($xml) {
		$db = MijnSqli::instance();
		$datum = getDateTime(); //invoerdatum voor hele sessie gelijk.


		$aSocciesaldi = simplexml_load_string($xml);
		//controleren of we wel een object krijgen:
		if (!is_object($aSocciesaldi)) {
			return 'Geen correcte XML ingevoerd! (Saldi::putSoccieXML())';
		}

		$iAantal = count($aSocciesaldi);
		$bOk = true;
		foreach ($aSocciesaldi as $aSocciesaldo) {
			$query = "SELECT uid FROM lid WHERE soccieID=" . $aSocciesaldo->id . "  AND createTerm='" . $aSocciesaldo->createTerm . "' LIMIT 1";
			$uidresult = $db->getRow($query);
			$uid = $uidresult['uid'];
			if (!Lid::exists($uid)) {
				continue;
			} //ignore niet-bestaande leden
			$query = "
				UPDATE lid
				SET soccieSaldo=" . $aSocciesaldo->saldo . "
				WHERE uid='" . $uid . "' LIMIT 1;";
			//sla het saldo ook op in een logje, zodat we later kunnen zien dat iemand al heel lang
			//rood staat en dus geschopt kan worden...
			$logQuery = "
				INSERT INTO saldolog (
					uid, moment, cie, saldo
				)VALUES(
					'" . $uid . "',
					'" . $datum . "',
					'soccie',
					" . $aSocciesaldo->saldo . "
				);";
			if (!$db->query($query)) {
				//scheids, er gaet een kwerie mis, ff een feutmelding printen.
				$bOk = false;
			} else {
				if (!$db->query($logQuery)) {
					echo '-! Koppeling voor ' . $aSocciesaldo->voornaam . ' ' . $aSocciesaldo->achternaam . ' mislukt' . "\r\n";
				} else {
					//LidCache resetten voor het betreffende lid
					LidCache::updateLid($uid);
				}
			}
		}
		if ($bOk) {
			return '[ ' . $iAantal . ' regels ontvangen.... OK ]';
		} else {
			return '[ tenminste 1 van ' . $iAantal . ' queries is niet gelukt. Laatste foutmelding was ' . $db->error() . ']';
		}
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
						Lid::isValidUid($aRegel[0]) AND is_numeric($aRegel[1])) {
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
						//LidCache resetten voor het betreffende lid
						try {
							LidCache::updateLid($aRegel[0]);
						} catch (Exception $e) {
							return 'Er bestaat een lid niet: ' . $e->getMessage();
						}
					} else {
						$bCorrect = false;
					}
					$row++;
				}
			}
			if ($bCorrect === true) {
				setMelding('Er zijn ' . $row . ' regels ingevoerd. Als dit er minder zijn dan u verwacht zitten er ongeldige regels in uw bestand.', 0);
			} else {
				setMelding('Helaas, er ging iets mis. Controleer uw bestand! mysql gaf terug <' . $db->error() . '>', -1);
			}
		}
	}

}
