<?php

require_once 'maalcie/model/KwalificatiesModel.class.php';
require_once 'maalcie/model/MaaltijdRepetitiesModel.class.php';

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
		$repetities = \CorveeRepetitiesModel::getAlleRepetities();
		foreach ($repetities as $repetitie) {
			\CorveeRepetitiesModel::verwijderRepetitie($repetitie->getCorveeRepetitieId());
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

	public static function archiveer() {
		$maaltijd = null;
		$aanmeldingen = array();
		$rows = self::queryDb('SELECT maalid, uid, door, gasten FROM maaltijdgesloten ORDER BY maalid');
		foreach ($rows as $row) {
			$mid = (int) $row['maalid'];
			if ($maaltijd === null || $maaltijd->getMaaltijdId() !== $mid) {
				if ($maaltijd !== null) {
					self::archiefMaaltijd(new ArchiefMaaltijd(
							$maaltijd->getMaaltijdId(), $maaltijd->getTitel(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $aanmeldingen
					));
					unset($maaltijd);
					unset($aanmeldingen);
				}
				echo '<br />' . date('H:i:s') . ' converteren: maaltijd (id: ' . $mid . ')';
				$maaltijd = self::queryDb('SELECT id, datum, type, tekst FROM maaltijd WHERE id="' . $mid . '"');
				if (sizeof($maaltijd) === 1) {
					$datum = intval($maaltijd[0]['datum']);
					$maaltijd = new Maaltijd($mid, null, $maaltijd[0]['tekst'], 0, date('Y-m-d', $datum), date('H:i', $datum));
					$aanmeldingen = array();
				} else {
					$maaltijd = new Maaltijd($mid, null, 'null', 0, date('Y-m-d', 0), date('H:i', 0));
					$aanmeldingen = array();
				}
			}
			$uid = $row['uid'];
			$door = $row['door'];
			if ($door === '') {
				$door = 'abo';
			}
			$aanmeldingen[$uid] = new MaaltijdAanmelding($mid, $uid, (int) $row['gasten'], '', null, $door, '');
		}
		if ($maaltijd !== null) {
			self::archiefMaaltijd(new ArchiefMaaltijd(
					$maaltijd->getMaaltijdId(), $maaltijd->getTitel(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $aanmeldingen
			));
			unset($maaltijd);
			unset($aanmeldingen);
		}
		unset($rows);
		$maaltijd = null;
		$aanmeldingen = array();
		$rows = self::queryDb('SELECT maalid, uid, status, door, gasten FROM maaltijdaanmelding ORDER BY maalid');
		foreach ($rows as $row) {
			$mid = (int) $row['maalid'];
			if ($maaltijd === null || $maaltijd->getMaaltijdId() !== $mid) {
				if ($row['status'] !== 'AAN') {
					continue;
				}
				if ($maaltijd !== null) {
					self::archiefMaaltijd(new ArchiefMaaltijd(
							$maaltijd->getMaaltijdId(), $maaltijd->getTitel(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $aanmeldingen
					));
					unset($maaltijd);
					unset($aanmeldingen);
				}
				echo '<br />' . date('H:i:s') . ' converteren: maaltijd (id: ' . $mid . ')';
				$maaltijd = self::queryDb('SELECT id, datum, type, tekst FROM maaltijd WHERE id="' . $mid . '"');
				if (sizeof($maaltijd) === 1) {
					$datum = intval($maaltijd[0]['datum']);
					$maaltijd = new Maaltijd($mid, null, $maaltijd[0]['tekst'], 0, date('Y-m-d', $datum), date('H:i', $datum));
					$aanmeldingen = array();
				} else {
					$maaltijd = new Maaltijd($mid, null, 'null', 0, date('Y-m-d', 0), date('H:i', 0));
					$aanmeldingen = array();
				}
			}
			$uid = $row['uid'];
			$door = $row['door'];
			if ($door === '') {
				$door = $uid;
			}
			$aanmeldingen[$uid] = new MaaltijdAanmelding($mid, $uid, (int) $row['gasten'], '', null, $door, '');
		}
		if ($maaltijd !== null) {
			self::archiefMaaltijd(new ArchiefMaaltijd(
					$maaltijd->getMaaltijdId(), $maaltijd->getTitel(), $maaltijd->getDatum(), $maaltijd->getTijd(), $maaltijd->getPrijs(), $aanmeldingen
			));
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
		$byFid = \FunctiesModel::getAlleFuncties();
		$rows = self::queryDb('SELECT * FROM maaltijdcorveeinstellingen');
		foreach ($rows as $row) {
			$id = $row['instelling'];
			if (array_key_exists($id, $functies)) {
				$fid = $functies[$id];
				$functie = $byFid[$fid];
				try {
					$byFid[$fid] = \FunctiesModel::saveFunctie($fid, $functie->getNaam(), $functie->getAfkorting(), $row['tekst'], $functie->getStandaardPunten(), false);
				} catch (\Exception $e) {
					
				}
				if ($fid === 1) { // email kwalikok
					$functie = $byFid[7];
					try {
						$byFid[7] = \FunctiesModel::saveFunctie(7, $functie->getNaam(), $functie->getAfkorting(), $row['tekst'], $functie->getStandaardPunten(), false);
					} catch (\Exception $e) {
						
					}
				} elseif ($fid === 2) { // email kwaliafwas
					$functie = $byFid[8];
					try {
						$byFid[8] = \FunctiesModel::saveFunctie(8, $functie->getNaam(), $functie->getAfkorting(), $row['tekst'], $functie->getStandaardPunten(), false);
					} catch (\Exception $e) {
						
					}
				}
			} elseif (array_key_exists($id, $punten)) {
				$fid = $punten[$id];
				$functie = $byFid[$fid];
				try {
					$byFid[$fid] = \FunctiesModel::saveFunctie($fid, $functie->getNaam(), $functie->getAfkorting(), $functie->getEmailBericht(), intval($row['int']), false);
				} catch (\Exception $e) {
					
				}
				if ($fid === 2) { // puntenkwaliafwas
					$functie = $byFid[8];
					try {
						$byFid[8] = \FunctiesModel::saveFunctie(8, $functie->getNaam(), $functie->getAfkorting(), $functie->getEmailBericht(), intval($row['int']), false);
					} catch (\Exception $e) {
						
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
			$filter = str_replace('Verticale ', 'verticale:', $row['tekst']);
			;
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
				$mrid_do = $rep->getMaaltijdRepetitieId();
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
			if ($fid === 7 || $fid === 3) {
				$vrk = false;
			}
			if ($fid > 3 && $fid !== 7 && $fid !== 8) {
				$periode = 28;
				if ($fid === 4) {
					$periode = 7;
				} elseif ($fid === 10 || $fid === 11) {
					$periode = 0;
				}
				$corvee[$fid] = \CorveeRepetitiesModel::saveRepetitie(0, null, 1, $periode, $fid, 1, $vrk);
				$corvee[$fid] = $corvee[$fid][0];
			} else {
				$corvee[$fid] = \CorveeRepetitiesModel::saveRepetitie(0, $mrid_do, 4, 7, $fid, 1, $vrk);
				$corvee[$fid] = $corvee[$fid][0];
			}
		}
		$corvee_wo = array();
		$corvee_wo[1] = \CorveeRepetitiesModel::saveRepetitie(0, $rep_wo->getMaaltijdRepetitieId(), $rep_wo->getDagVanDeWeek(), $rep_wo->getPeriodeInDagen(), 1, 1, true);
		$corvee_wo[1] = $corvee_wo[1][0];
		$corvee_wo[2] = \CorveeRepetitiesModel::saveRepetitie(0, $rep_wo->getMaaltijdRepetitieId(), $rep_wo->getDagVanDeWeek(), $rep_wo->getPeriodeInDagen(), 2, 1, true);
		$corvee_wo[2] = $corvee_wo[2][0];
		$corvee_wo[8] = \CorveeRepetitiesModel::saveRepetitie(0, $rep_wo->getMaaltijdRepetitieId(), $rep_wo->getDagVanDeWeek(), $rep_wo->getPeriodeInDagen(), 8, 1, true);
		$corvee_wo[8] = $corvee_wo[8][0];
		$corvee_wo[7] = \CorveeRepetitiesModel::saveRepetitie(0, $rep_wo->getMaaltijdRepetitieId(), $rep_wo->getDagVanDeWeek(), $rep_wo->getPeriodeInDagen(), 7, 1, true);
		$corvee_wo[7] = $corvee_wo[7][0];

		echo '<br />' . date('H:i:s') . ' converteren: vrijstelling => CorveeVrijstelling & kwalikok => CorveeKwalificatie & voorkeuren => CorveeVoorkeur';

		$rows = self::queryDb('SELECT uid, corvee_vrijstelling, corvee_kwalikok, corvee_voorkeuren FROM lid');
		foreach ($rows as $row) {
			$percentage = intval($row['corvee_vrijstelling']);
			if ($percentage > 0) {
				try {
					\CorveeVrijstellingenModel::saveVrijstelling($row['uid'], date('Y-m-d'), date('Y-m-d', strtotime('+2 years')), $percentage);
				} catch (\Exception $e) {
					
				}
			}
			if ($row['corvee_kwalikok'] === '1') {
				\KwalificatiesModel::kwalificatieToewijzen(7, $row['uid']);
			}
			$vrk = str_split($row['corvee_voorkeuren']);
			if (array_key_exists(0, $vrk) && $vrk[0] === '1') { // klussen licht
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[10]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(1, $vrk) && $vrk[1] === '1') { // klussen zwaar
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[11]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(2, $vrk) && $vrk[2] === '1') { // woensdag koken
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee_wo[1]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(3, $vrk) && $vrk[3] === '1') { // woensdag kwaliafwassen
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee_wo[2]->getCorveeRepetitieId(), $row['uid']);
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee_wo[8]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(4, $vrk) && $vrk[4] === '1') { // donderdag koken
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[1]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(5, $vrk) && $vrk[5] === '1') { // donderdag afwassen & kwaliafwassen
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[2]->getCorveeRepetitieId(), $row['uid']);
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[8]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(6, $vrk) && $vrk[6] === '1') { // theedoeken wassen
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[4]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(7, $vrk) && $vrk[7] === '1') { // schoonmaken afzuigkap
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[6]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(8, $vrk) && $vrk[8] === '1') { // schoonmaken frituur
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[9]->getCorveeRepetitieId(), $row['uid']);
			}
			if (array_key_exists(9, $vrk) && $vrk[9] === '1') { // schoonmaken keuken
				\CorveeVoorkeurenModel::inschakelenVoorkeur($corvee[5]->getCorveeRepetitieId(), $row['uid']);
			}
		}

		echo '<br />' . date('H:i:s') . ' converteren: maaltijd => Maaltijd & maaltijdcorvee => CorveeTaak[]';

		$aantallen = array(
			7 => 'kwalikoks',
			1 => 'koks',
			2 => 'afwassers',
			4 => 'theedoeken',
			9 => 'schoonmaken_frituur',
			6 => 'schoonmaken_afzuigkap',
			5 => 'schoonmaken_keuken',
			10 => 'klussen_licht',
			11 => 'klussen_zwaar'
		);
		$rows = self::queryDb('SELECT * FROM maaltijd');
		$maaltijden = array();
		foreach ($rows as $row) {
			$mid = null;
			$datum = intval($row['datum']);
			$gemailed = intval($row['corvee_gemaild']);
			if ($datum < time()) {
				continue;
			}
			if ($row['type'] === 'normaal') {
				if ($row['gesloten'] === '1') {
					continue;
				}
				$mrid = null;
				$crid = null;
				$titel = $row['tekst'];
				$filter = '';
				if (array_key_exists($row['abosoort'], $repetities)) {
					$mrid = $repetities[$row['abosoort']]->getMaaltijdRepetitieId();
					$filter = $repetities[$row['abosoort']]->getAbonnementFilter();
				}
				if ($titel === 'Alpha-Cursus') {
					$titel = 'Alpha-cursus';
					$mrid = $rep_wo->getMaaltijdRepetitieId();
				}
				if ($titel === 'Donderdag') {
					$titel .= 'maaltijd';
					$crid = $corvee[3]->getCorveeRepetitieId();
				}
				$maaltijd = self::conversieMaaltijd(intval($row['id']), $mrid, $titel, intval($row['max']), date('Y-m-d', $datum), date('H:i', $datum), intval(Instellingen::get('maaltijden', 'standaard_prijs')), $filter);
				$mid = $maaltijd->getMaaltijdId();
				$maaltijden[$mid] = $maaltijd;

				$uid = $row['tp'];
				if ($uid === 'x101') {
					$uid = null;
				}
				if ($titel !== 'Alpha-cursus') {
					$corveetaak = \CorveeTakenModel::saveTaak(0, 3, $uid, $crid, $mid, date('Y-m-d', $datum), 0, 0);
					for ($i = 0; $i < $gemailed; $i++) {
						\CorveeTakenModel::updateGemaild($corveetaak);
					}
				}
			}

			foreach ($functies as $functie => $fid) {
				if ($fid === 3) {
					continue;
				}
				$taken = self::queryDb('SELECT * FROM maaltijdcorvee WHERE maalid = ?', array($mid));
				if ($fid === 8) {
					$punt = intval($row['punten_afwas']);
				} else {
					$punt = intval($row['punten_' . $functie]);
				}
				$aantal = 0;
				foreach ($taken as $taak) {
					if ($taak[$functie] === '1') {
						$aantal++;
						$uid = $taak['uid'];
						if ($uid === 'x101') {
							$uid = null;
						}
						$crid = $corvee[$fid]->getCorveeRepetitieId();
						if (date('w', $datum) == 3) {
							$crid = null;
							if (array_key_exists($fid, $corvee_wo)) {
								$crid = $corvee_wo[$fid]->getCorveeRepetitieId();
							}
						}
						$corveetaak = \CorveeTakenModel::saveTaak(0, $fid, $uid, $crid, $mid, date('Y-m-d', $datum), $punt, 0);
						if ($taak['punten_toegekend'] === 'ja') {
							\CorveeTakenModel::puntenToekennen($corveetaak);
						}
						for ($i = 0; $i < $gemailed; $i++) {
							\CorveeTakenModel::updateGemaild($corveetaak);
						}
					}
				}
				if ($fid === 8) {
					if ($aantal === 0 && $mid !== null) {
						$tekort = 1;
					}
				} else {
					$tekort = intval($row[$aantallen[$fid]]) - $aantal;
				}
				if ($fid === 2 && $tekort > 0) {
					$tekort--;
				}
				$crid = $corvee[$fid]->getCorveeRepetitieId();
				if (date('w', $datum) == 3) {
					$crid = null;
					if (array_key_exists($fid, $corvee_wo)) {
						$crid = $corvee_wo[$fid]->getCorveeRepetitieId();
					}
				}
				for ($i = 0; $i < $tekort; $i++) {
					$corveetaak = \CorveeTakenModel::saveTaak(0, $fid, null, $crid, $mid, date('Y-m-d', $datum), $punt, 0);
				}
			}
		}

		echo '<br />' . date('H:i:s') . ' converteren: maaltijdaanmelding => MaaltijdAanmelding[]';

		$rows = self::queryDb('SELECT * FROM maaltijdaanmelding');
		foreach ($rows as $row) {
			if ($row['status'] === 'AAN') {
				try {
					MaaltijdAanmeldingenModel::aanmeldenVoorMaaltijd(intval($row['maalid']), $row['uid'], $row['door'], intval($row['gasten']), true, $row['gasten_eetwens']);
				} catch (\Exception $e) {
					
				}
			}
		}

		echo '<br />' . date('H:i:s') . ' converteren: maaltijdabo => MaaltijdAbonnement';

		$rows = self::queryDb('SELECT uid, abosoort FROM maaltijdabo');
		foreach ($rows as $row) {
			if (array_key_exists($row['abosoort'], $repetities)) {
				MaaltijdAbonnementenModel::inschakelenAbonnement($repetities[$row['abosoort']]->getMaaltijdRepetitieId(), $row['uid']);
			}
		}

		echo '<br />' . date('H:i:s') . ' converteren voltooid';
	}

	private static function queryDb($sql, $values = array()) {
		$db = \Database::instance();
		$query = $db->prepare($sql);
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
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('New maaltijd faalt: $query->rowCount() =' . $query->rowCount());
		}
		$maaltijd = new Maaltijd($mid, $mrid, $titel, $limiet, $datum, $tijd, $prijs, false, null, false, $filter);
		$maaltijd->setAantalAanmeldingen(0);
		return $maaltijd;
	}

	private static function archiefMaaltijd(ArchiefMaaltijd $archief) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$sql = 'INSERT INTO mlt_archief';
			$sql.= ' (maaltijd_id, titel, datum, tijd, prijs, aanmeldingen)';
			$sql.= ' VALUES (?, ?, ?, ?, ?, ?)';
			$values = array(
				$archief->getMaaltijdId(),
				$archief->getTitel(),
				$archief->getDatum(),
				$archief->getTijd(),
				$archief->getPrijs(),
				$archief->getAanmeldingen()
			);
			$query = $db->prepare($sql);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				$db->rollback();
				throw new Exception('New archief-maaltijd faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}

?>