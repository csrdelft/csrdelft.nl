<?php
namespace Taken\MLT;

require_once 'taken/model/KwalificatiesModel.class.php';
require_once 'taken/model/MaaltijdRepetitiesModel.class.php';

/**
 * ConversieModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class ConversieModel {

	public static function leegmaken() {
		
		echo '<br />' . date('H:i:s') . ' leegmaken crv_tabellen';
		
		self::queryDb('TRUNCATE TABLE crv_kwalificaties');
		self::queryDb('TRUNCATE TABLE crv_vrijstellingen');
		self::queryDb('TRUNCATE TABLE crv_voorkeuren');
		self::queryDb('TRUNCATE TABLE crv_taken');
		$repetities = \Taken\CRV\CorveeRepetitiesModel::getAlleRepetities();
		foreach ($repetities as $repetitie) {
			\Taken\CRV\CorveeRepetitiesModel::verwijderRepetitie($repetitie->getCorveeRepetitieId());
		}
		
		echo '<br />' . date('H:i:s') . ' leegmaken mlt_tabellen';
		
		self::queryDb('TRUNCATE TABLE mlt_aanmeldingen');
		self::queryDb('TRUNCATE TABLE mlt_abonnementen');
		$maaltijden = MaaltijdenModel::getAlleMaaltijden();
		foreach ($maaltijden as $maaltijd) {
			MaaltijdenModel::verwijderMaaltijd($maaltijd->getMaaltijdId());
			MaaltijdenModel::verwijderMaaltijd($maaltijd->getMaaltijdId());
		}
		$repetities = MaaltijdRepetitiesModel::getAlleRepetities();
		foreach ($repetities as $repetitie) {
			MaaltijdRepetitiesModel::verwijderRepetitie($repetitie->getMaaltijdRepetitieId());
		}
	}
	
	public static function converteer() {
		
		echo '<br />' . date('H:i:s') . ' converteren: maaltijdcorveeinstelligen => CorveeFunctie[]';
		
		$functies = array(
			'koks' => 1,
			'afwas' => 2,
			'theedoeken' => 4,
			'frituur' => 9,
			'afzuigkap' => 6,
			'keuken' => 5,
			'lichteklus' => 10,
			'zwareklus' => 11,
			'tafelp' => 3
		);
		$punten = array(
			'puntenkwalikoken' => 7,
			'puntenkoken' => 1,
			'puntenafwas' => 2,
			'puntentheedoek' => 4,
			'puntenfrituur' => 9,
			'puntenafzuigkap' => 6,
			'puntenkeuken' => 5,
			'puntenlichteklus' => 10,
			'puntenzwareklus' => 11,
		);
		$byFid = \Taken\CRV\FunctiesModel::getAlleFuncties(true);
		$rows = self::queryDb('SELECT * FROM maaltijdcorveeinstellingen');
		foreach ($rows as $row) {
			$id = $row['instelling'];
			if (array_key_exists($id, $functies)) {
				$fid = $functies[$id];
				$functie = $byFid[$fid];
				try {
					$byFid[$fid] = \Taken\CRV\FunctiesModel::saveFunctie($fid, $functie->getNaam(), $functie->getAfkorting(), $functie->getOmschrijving(), $row['tekst'], $functie->getStandaardPunten(), false);
				}
				catch (\Exception $e) {
				}
				if ($fid === 1) { // email kwalikok
					$functie = $byFid[7];
					try {
						$byFid[7] = \Taken\CRV\FunctiesModel::saveFunctie(7, $functie->getNaam(), $functie->getAfkorting(), $functie->getOmschrijving(), $row['tekst'], $functie->getStandaardPunten(), false);
					}
					catch (\Exception $e) {
					}
				}
				elseif ($fid === 2) { // email kwaliafwas
					$functie = $byFid[8];
					try {
						$byFid[8] = \Taken\CRV\FunctiesModel::saveFunctie(8, $functie->getNaam(), $functie->getAfkorting(), $functie->getOmschrijving(), $row['tekst'], $functie->getStandaardPunten(), false);
					}
					catch (\Exception $e) {
					}
				}
			}
			elseif (array_key_exists($id, $punten)) {
				$fid = $punten[$id];
				$functie = $byFid[$fid];
				try {
					$byFid[$fid] = \Taken\CRV\FunctiesModel::saveFunctie($fid, $functie->getNaam(), $functie->getAfkorting(), $functie->getOmschrijving(), $functie->getEmailBericht(), intval($row['int']), false);
				}
				catch (\Exception $e) {
				}
				if ($fid === 2) { // puntenkwaliafwas
					$functie = $byFid[8];
					try {
						$byFid[8] = \Taken\CRV\FunctiesModel::saveFunctie(8, $functie->getNaam(), $functie->getAfkorting(), $functie->getOmschrijving(), $functie->getEmailBericht(), intval($row['int']), false);
					}
					catch (\Exception $e) {
					}
				}
			}
		}
		
		echo '<br />' . date('H:i:s') . ' converteren: abosoort => MaaltijdRepetitie';
		
		$rows = self::queryDb('SELECT abosoort, tekst FROM maaltijdabosoort');
		$default = new MaaltijdRepetitie();
		$repetities = array();
		foreach ($rows as $row) {
			if ($row['abosoort'] === 'A_GEEN') {
				continue;
			}
			$dag = 2;
			$periode = 0;
			$limiet = 40;
			$filter = str_replace('Verticale ', 'verticale:', $row['tekst']);;
			$titel = str_replace('Verticale', 'Grootfeest', $row['tekst']);
			if ($row['abosoort'] === 'A_DONDERDAG') {
				$dag = $default->getDagVanDeWeek();
				$periode = $default->getPeriodeInDagen();
				$limiet = 100;
				$filter = '';
				$titel .= 'maaltijd';
			}
			if ($row['abosoort'] === 'A_VROUW') {
				$filter = 'geslacht:v';
				$titel = 'DéDé-diner';
			}
			$rep = MaaltijdRepetitiesModel::saveRepetitie(0, $dag, $periode, $titel, $default->getStandaardTijd(), $default->getStandaardPrijs(), $default->getIsAbonneerbaar(), $limiet, $filter);
			$rep = $rep[0];
			$repetities[$row['abosoort']] = $rep;
			if ($row['abosoort'] === 'A_DONDERDAG') {
				$mrid = $rep->getMaaltijdRepetitieId();
			}
		}
		$rep_wo = MaaltijdRepetitiesModel::saveRepetitie(0, 3, 7, 'Alpha-cursus', '18:30', $default->getStandaardPrijs(), false, 1, '');
		$rep_wo = $rep_wo[0];
		
		echo '<br />' . date('H:i:s') . ' aanmaken: CorveeRepetitie[]';
		
		$functies = array(
			'kwalikok' => 7,
			'kwaliafwas' => 8,
			'kok' => 1,
			'afwas' => 2,
			'theedoek' => 4,
			'schoonmaken_frituur' => 9,
			'schoonmaken_afzuigkap' => 6,
			'schoonmaken_keuken' => 5,
			'klussen_licht' => 10,
			'klussen_zwaar' => 11,
			'tp' => 3
		);
		$corvee = array();
		foreach ($functies as $functie => $fid) {
			$vrk = true;
			if ($fid === 7) {
				$vrk = false;
			}
			$corvee[$fid] = \Taken\CRV\CorveeRepetitiesModel::saveRepetitie(0, $mrid, 4, 7, $fid, 1, $vrk);
			$corvee[$fid] = $corvee[$fid][0];
		}
		$corvee_wo = array();
		$corvee_wo[1] = \Taken\CRV\CorveeRepetitiesModel::saveRepetitie(0, $rep_wo->getMaaltijdRepetitieId(), $rep_wo->getDagVanDeWeek(), $rep_wo->getPeriodeInDagen(), 1, 1, true);
		$corvee_wo[1] = $corvee_wo[1][0];
		$corvee_wo[2] = \Taken\CRV\CorveeRepetitiesModel::saveRepetitie(0, $rep_wo->getMaaltijdRepetitieId(), $rep_wo->getDagVanDeWeek(), $rep_wo->getPeriodeInDagen(), 2, 1, true);
		$corvee_wo[2] = $corvee_wo[2][0];
		
		echo '<br />' . date('H:i:s') . ' converteren: vrijstelling => CorveeVrijstelling & kwalikok => CorveeKwalificatie & voorkeuren => CorveeVoorkeur';
		
		$rows = self::queryDb('SELECT uid, corvee_vrijstelling, corvee_kwalikok, corvee_voorkeuren FROM lid');
		foreach ($rows as $row) {
			$percentage = intval($row['corvee_vrijstelling']);
			if ($percentage > 0) {
				try {
					\Taken\CRV\VrijstellingenModel::saveVrijstelling($row['uid'], date('Y-m-d'), date('Y-m-d', strtotime('+2 years')), $percentage);
				}
				catch (\Exception $e) {
				}
			}
			if ($row['corvee_kwalikok'] === '1') {
				\Taken\CRV\KwalificatiesModel::kwalificatieToewijzen(7, $row['uid']);
			}
			$vrk = str_split($row['corvee_voorkeuren']);
			if (array_key_exists(0, $vrk) && $vrk[0] === '1') { // klussen licht
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[10]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(1, $vrk) && $vrk[1] === '1') { // klussen zwaar
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[11]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(2, $vrk) && $vrk[2] === '1') { // woensdag koken
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee_wo[1]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(3, $vrk) && $vrk[3] === '1') { // woensdag afwassen
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee_wo[2]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(4, $vrk) && $vrk[4] === '1') { // donderdag koken
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[1]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(5, $vrk) && $vrk[5] === '1') { // donderdag afwassen
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[2]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(6, $vrk) && $vrk[6] === '1') { // theedoeken wassen
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[4]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(7, $vrk) && $vrk[7] === '1') { // schoonmaken afzuigkap
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[6]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(8, $vrk) && $vrk[8] === '1') { // schoonmaken frituur
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[9]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(9, $vrk) && $vrk[9] === '1') { // schoonmaken keuken
				\Taken\CRV\VoorkeurenModel::inschakelenVoorkeur($corvee[5]->getCorveeRepetitieId(), $row['uid']);
			}
		}
		
		echo '<br />' . date('H:i:s') . ' converteren: maaltijd => Maaltijd & maaltijdcorvee => CorveeTaak[]';
		
		$rows = self::queryDb('SELECT * FROM maaltijd');
		$maaltijden = array();
		foreach ($rows as $row) {
			$mid = null;
			$datum = intval($row['datum']);
			if ($datum < strtotime('-1 day')) {
				continue;
			}
			if ($row['type'] === 'normaal') {
				if ($row['gesloten'] === '1') {
					continue;
				}
				$mrid = null;
				$titel = $row['tekst'];
				$filter = '';
				if (array_key_exists($row['abosoort'], $repetities)) {
					$mrid = $repetities[$row['abosoort']]->getMaaltijdRepetitieId();
					$filter = $repetities[$row['abosoort']]->getAbonnementFilter();
				}
				if ($titel === 'Alpha-Cursus') {
					$mrid = $rep_wo->getMaaltijdRepetitieId();
					$titel = 'Alpha-cursus';
				}
				if ($titel === 'Donderdag') {
					$titel .= 'maaltijd';
				}
				$maaltijd = self::conversieMaaltijd(intval($row['id']), $mrid, $titel, intval($row['max']), date('Y-m-d', $datum), date('H:i', $datum), floatval($GLOBALS['standaard_maaltijdprijs']), $filter);
				$mid = $maaltijd->getMaaltijdId();
				$maaltijden[$mid] = $maaltijd;
				
				$corveetaak = \Taken\CRV\TakenModel::saveTaak(0, 3, $row['tp'], $corvee[3]->getCorveeRepetitieId(), $mid, date('Y-m-d', $datum), 0, 0);
				\Taken\CRV\TakenModel::puntenToekennen($corveetaak);
			}
			
			foreach ($functies as $functie => $fid) {
				if ($fid === 3) {
					continue;
				}
				
				$taken = self::queryDb('SELECT * FROM maaltijdcorvee WHERE maalid = ?', array($mid));
				foreach ($taken as $taak) {
					if ($fid === 8) {
						$punt = intval($row['punten_afwas']);
					}
					else {
						$punt = intval($row['punten_'. $functie]);
					}
					$corveetaak = \Taken\CRV\TakenModel::saveTaak(0, $fid, $taak['uid'], $corvee[$fid]->getCorveeRepetitieId(), $mid, date('Y-m-d', $datum), $punt, 0);
					if ($taak['punten_toegekend'] === 'ja') {
						\Taken\CRV\TakenModel::puntenToekennen($corveetaak);
					}
				}
			}
		}
		
		echo '<br />' . date('H:i:s') . ' converteren: maaltijdaanmelding => MaaltijdAanmelding[]';
		
		$rows = self::queryDb('SELECT * FROM maaltijdaanmelding');
		foreach ($rows as $row) {
			if ($row['status'] === 'AAN') {
				try {
					AanmeldingenModel::aanmeldenVoorMaaltijd(intval($row['maalid']), $row['uid'], $row['door'], intval($row['gasten']), true, $row['gasten_opmerking']);
				}
				catch (\Exception $e) {
				}
			}
		}
		
		echo '<br />' . date('H:i:s') . ' converteren: maaltijdabo => MaaltijdAbonnement';
		
		$rows = self::queryDb('SELECT uid, abosoort FROM maaltijdabo');
		foreach ($rows as $row) {
			if (array_key_exists($row['abosoort'], $repetities)) {
				AbonnementenModel::inschakelenAbonnement($repetities[$row['abosoort']]->getMaaltijdRepetitieId(), $row['uid']);
			}
		}
		
		echo '<br />' . date('H:i:s') . ' converteren voltooid';
	}
	
	private static function queryDb($sql, $values=array()) {
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() > 0) {
			$result = $query->fetchAll();
			return $result;
		}
		return array();
	}
	
	private static function conversieMaaltijd($mid, $mrid, $titel, $limiet, $datum, $tijd, $prijs, $filter) {
		$sql = 'INSERT INTO mlt_maaltijden';
		$sql.= ' (maaltijd_id, mlt_repetitie_id, titel, aanmeld_limiet, datum, tijd, prijs, gesloten, laatst_gesloten, verwijderd, aanmeld_filter)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$values = array($mid, $mrid, $titel, $limiet, $datum, $tijd, $prijs, false, null, false, $filter);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('New maaltijd faalt: $query->rowCount() ='. $query->rowCount());
		}
		$maaltijd = new Maaltijd($mid, $mrid, $titel, $limiet, $datum, $tijd, $prijs, false, null, false, $filter);
		$maaltijd->setAantalAanmeldingen(0);
		return $maaltijd;
	}
}

?>