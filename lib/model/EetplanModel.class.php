<?php

/**
 * EetplanModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 * Verzorgt het opvragen van eetplangegevens
 */
class EetplanModel {

	function getDatum($iAvond) {
		$aAvonden = array(
			'28-10-2014',
			'25-11-2014',
			'23-12-2014',
			'07-01-2014'
		);
		if ($iAvond > 0 AND $iAvond <= sizeof($aAvonden)) {
			return $aAvonden[$iAvond - 1];
		}
	}

	function getEetplan() {
		//huizen laden
		$rEetplan = MijnSqli::instance()->select("
			SELECT
  			eetplan.uid AS uid,
  			eetplan.huis AS huis,
  			eetplan.avond AS avond
			FROM
				eetplan
			INNER JOIN lid ON(eetplan.uid=lid.uid)
			ORDER BY
 				lid.achternaam, uid, avond;"
		);
		$aEetplan = array();
		$aEetplanRegel = array();
		while ($aEetplanData = MijnSqli::instance()->next($rEetplan)) {
			//nieuwe regel beginnen als nodig
			if ($aEetplanData['avond'] == 1) {
				$aEetplan[] = $aEetplanRegel;
				$aEetplanRegel = array();
				$lid = LidCache::getLid($aEetplanData['uid']);
				//eerste element van de regel is het uid
				$aEetplanRegel[] = array(
					'uid'	 => $aEetplanData['uid'],
					'naam'	 => (string) $lid);
			}
			$aEetplanRegel[] = $aEetplanData['huis'];
		}
		//ook de laaste regel toevoegen
		$aEetplan[] = $aEetplanRegel;
		//eerste regel eruit slopen, die is toch nutteloos.
		unset($aEetplan[0]);
		return $aEetplan;
	}

	function getEetplanVoorPheut($iPheutID) {
		if (!Lid::isValidUid($iPheutID)) {
			return false;
		}
		$sEetplanQuery = "
			SELECT DISTINCT
				eetplan.avond AS avond,
				eetplanhuis.id AS huisID,
				eetplanhuis.naam AS huisnaam,
				eetplanhuis.adres AS huisadres,
				eetplanhuis.groepid AS groepid,
				eetplanhuis.telefoon AS telefoon
			FROM
				eetplanhuis, eetplan
			WHERE
				eetplan.huis=eetplanhuis.id AND
				eetplan.uid='" . MijnSqli::instance()->escape($iPheutID) . "'
			ORDER BY
				eetplan.avond;";
		$rEetplanVoorPheut = MijnSqli::instance()->select($sEetplanQuery);
		if (MijnSqli::instance()->numRows($rEetplanVoorPheut) == 0) {
			//deze feut bestaat niet
			return false;
		} else {
			$aEetplan = array();
			while ($aEetplanData = MijnSqli::instance()->next($rEetplanVoorPheut)) {
				$aEetplan[] = $aEetplanData;
			}
			return $aEetplan;
		}
	}

	function getEetplanVoorHuis($iHuisID) {
		$db = MijnSqli::instance();
		$sEetplanQuery = "
			SELECT DISTINCT
				eetplan.avond AS avond,
				eetplanhuis.naam AS huisnaam,
				eetplanhuis.adres AS huisadres,
				eetplanhuis.telefoon AS huistelefoon,
				eetplanhuis.groepid AS groepid,
				eetplan.uid AS pheut,
				lid.eetwens AS eetwens,
				lid.mobiel AS mobiel,
				lid.email AS email
			FROM
				eetplanhuis, eetplan
			INNER JOIN lid ON(eetplan.uid=lid.uid)
			WHERE
				eetplan.huis=eetplanhuis.id AND
				eetplanhuis.id=" . $iHuisID . "
			ORDER BY
				eetplan.avond;";
		$rEetplanVoorHuis = MijnSqli::instance()->select($sEetplanQuery);
		if (MijnSqli::instance()->numRows($rEetplanVoorHuis) == 0) {
			//geen huis met dit ID
			return false;
		} else {
			$aEetplan = array();
			while ($aEetplanData = MijnSqli::instance()->next($rEetplanVoorHuis)) {
				$aEetplan[] = $aEetplanData;
			}
			return $aEetplan;
		}
	}

	function getHuizen() {
		$sHuizenQuery = "
			SELECT DISTINCT
				id AS huisID,
				naam AS huisNaam,
				groepid
			FROM
				eetplanhuis
			ORDER BY
				id;";
		$rHuizen = MijnSqli::instance()->select($sHuizenQuery);
		while ($aHuizenData = MijnSqli::instance()->next($rHuizen)) {
			$aHuizen[] = $aHuizenData;
		}
		return $aHuizen;
	}

}
