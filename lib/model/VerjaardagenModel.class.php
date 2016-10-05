<?php

/**
 * VerjaardagenModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class VerjaardagenModel {

	public static function get($maand, $dag = 0) {
		$db = MijnSqli::instance();
		$maand = (int) $maand;
		$dag = (int) $dag;
		$verjaardagen = array();
		$query = "
			SELECT uid, voornaam, tussenvoegsel, achternaam, nickname, duckname, postfix, geslacht, email,
				EXTRACT( DAY FROM gebdatum) as gebdag, status
			FROM profielen
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')
			AND
				EXTRACT( MONTH FROM gebdatum)= '{$maand}'";
		if ($dag != 0)
			$query.=" AND gebdag=" . $dag;
		$query.=" ORDER BY gebdag;";
		$result = $db->select($query);

		if ($result !== false and $db->numRows($result) > 0) {
			while ($verjaardag = $db->next($result)) {
				$verjaardagen[] = $verjaardag;
			}
		}
		return $verjaardagen;
	}

	public static function getKomende($aantal = 10) {
		$db = MijnSqli::instance();
		$query = "
			SELECT uid, nickname, duckname, voornaam, tussenvoegsel, achternaam, status, geslacht, postfix, gebdatum,
				ADDDATE(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) AS verjaardag
			FROM profielen
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')
			AND
				NOT gebdatum = '0000-00-00'
			ORDER BY verjaardag ASC, lidjaar, gebdatum, achternaam
			LIMIT " . (int) $aantal;

		$leden = $db->query2array($query);

		$return = array();
		if (is_array($leden)) {
			foreach ($leden as $uid) {
				$return[] = ProfielModel::get($uid['uid']);
			}
		}
		return $return;
	}

	public static function getTussen($van, $tot, $limiet = 0) {
		$vanjaar = date('Y', $van);
		$totjaar = date('Y', $tot);
		$van = date('Y-m-d', $van);
		$tot = date('Y-m-d', $tot);

		if ((int) $limiet > 0) {
			$limitclause = "LIMIT " . (int) $limiet;
		} else {
			$limitclause = '';
		}
		$query = "
			SELECT uid,
				ADDDATE(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) as verjaardag
			FROM profielen
			WHERE (
				(CONCAT('" . $vanjaar . "', SUBSTRING(gebdatum, 5))>='" . $van . "' AND CONCAT('" . $vanjaar . "', SUBSTRING(gebdatum, 5))<'" . $tot . "')
			OR
				(CONCAT('" . $totjaar . "', SUBSTRING(gebdatum, 5))>='" . $van . "' AND CONCAT('" . $totjaar . "', SUBSTRING(gebdatum, 5))<'" . $tot . "')
			) AND
			(status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL') AND
			NOT gebdatum = '0000-00-00'
			ORDER BY verjaardag ASC, lidjaar, gebdatum, achternaam
			" . $limitclause . ";";

		$leden = MijnSqli::instance()->query2array($query);

		$return = array();
		if (is_array($leden)) {
			foreach ($leden as $uid) {
				$return[] = ProfielModel::get($uid['uid']);
			}
		}
		return $return;
	}

}
