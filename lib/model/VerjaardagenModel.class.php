<?php

/**
 * VerjaardagenModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class VerjaardagenModel {

	static function getVerjaardagen($maand, $dag = 0) {
		$db = MijnSqli::instance();
		$maand = (int) $maand;
		$dag = (int) $dag;
		$verjaardagen = array();
		$query = "
			SELECT
				uid, voornaam, tussenvoegsel, achternaam, nickname, duckname, postfix, geslacht, email,
				EXTRACT( DAY FROM gebdatum) as gebdag, status
			FROM
				lid
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

	static function getKomendeVerjaardagen($aantal = 10) {
		$aantal = (int) $aantal;
		$db = MijnSqli::instance();
		$query = "
			SELECT
				uid, nickname, duckname, voornaam, tussenvoegsel, achternaam, status, geslacht, postfix, gebdatum,
				ADDDATE(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) AS verjaardag
			FROM
				lid
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')
			AND
				NOT gebdatum = '0000-00-00'
			ORDER BY verjaardag ASC, lidjaar, gebdatum, achternaam
			LIMIT " . $aantal;

		$result = $db->select($query);

		if ($result !== false and $db->numRows($result) > 0) {
			while ($aVerjaardag = $db->next($result)) {
				$aVerjaardag['jarig_over'] = (int) ceil((strtotime($aVerjaardag['verjaardag']) - time()) / 86400);
				$aVerjaardag['leeftijd'] = round((strtotime($aVerjaardag['verjaardag']) - strtotime($aVerjaardag['gebdatum'])) / 31536000);
				$aVerjaardagen[] = $aVerjaardag;
			}
		}
		return $aVerjaardagen;
	}

}
