<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once 'class.ldap.php';
require_once 'class.memcached.php';

class Verjaardag{

	function getVerjaardagen($maand, $dag=0) {
		$db=MySql::instance();
		$maand = (int)$maand; $dag = (int)$dag; $verjaardagen = array();
		$query="
			SELECT
				uid, voornaam, tussenvoegsel, achternaam, nickname, postfix, geslacht, email,
				EXTRACT( DAY FROM gebdatum) as gebdag, status
			FROM
				lid
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')
			AND
				EXTRACT( MONTH FROM gebdatum)= '{$maand}'";
		if($dag!=0)	$query.=" AND gebdag=".$dag;
		$query.=" ORDER BY gebdag;";
		$result = $db->select($query);

		if ($result !== false and $db->numRows($result) > 0) {
			while($verjaardag=$db->next($result)){
				$verjaardagen[] = $verjaardag;
			}
		}
		return $verjaardagen;
	}

	function getKomendeVerjaardagen($aantal=10){
		$aantal=(int)$aantal;
		$db=MySql::instance();
		$query="
			SELECT
				uid, nickname, voornaam, tussenvoegsel, achternaam, status, geslacht, postfix, gebdatum,
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
			LIMIT ".$aantal;

		$result = $db->select($query);

		if ($result !== false and $db->numRows($result) > 0) {
			while($aVerjaardag=$db->next($result)){
				$aVerjaardag['jarig_over'] = ceil((strtotime($aVerjaardag['verjaardag'])-time())/86400);
				$aVerjaardag['leeftijd'] = round((strtotime($aVerjaardag['verjaardag'])-strtotime($aVerjaardag['gebdatum']))/31536000);
				$aVerjaardagen[] = $aVerjaardag;
			}
		}
		return $aVerjaardagen;
	}
}
class Moot{

	static function getMaxKringen($moot=0) {
		$db=MySql::instance();
		$maxkringen = 0;
		$sMaxKringen="
			SELECT
				MAX(kring) as max
			FROM
				lid
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') ";
		if($moot!=0){ $sMaxKringen.="AND moot=".$moot; }
		$sMaxKringen.="	LIMIT 1;";

		$result = $db->select($sMaxKringen);
		if ($result !== false and $db->numRows($result) > 0) {
			$max = $db->next($result);
			$maxkringen = $max['max'];
			return $maxkringen;
		}else{
			return 0;
		}
	}

	static function getMaxMoten() {
		$db=MySql::instance();
		$query="
			SELECT MAX(moot) as max
			FROM lid
			WHERE (status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL')";
		$max=$db->getRow($query);
		if(is_array($max)){
			return $max['max'];
		}
        return 0;
	}

	static function getKringen() {
		$db=MySql::instance();
		$kring = array();
		$result = $db->select("
			SELECT
				lid.uid as uid,
				nickname,
				voornaam,
				tussenvoegsel,
				achternaam,
				geslacht,
				postfix,
				moot,
				kring,
				motebal,
				kringleider,
				email,
				status,
				soccieSaldo
			FROM
				lid
			WHERE
				(status='S_LID' OR status='S_GASTLID' OR status='S_NOVIET' OR status='S_KRINGEL') OR
				((status='S_OUDLID' OR status='S_NOBODY') AND kring <> 0)
			ORDER BY
				kringleider DESC,
				achternaam ASC;");
		if ($result !== false and $db->numRows($result) > 0) {
			while ($lid = $db->next($result)) {
				$kring[$lid['moot']][$lid['kring']][] = $lid;
			}
		}

		return $kring;
	}
}
?>
