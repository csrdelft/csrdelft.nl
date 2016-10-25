<?php

/**
 * VerjaardagenModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 */
class VerjaardagenModel {

	public static function get($maand) {
        return ProfielModel::instance()->find("status in ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND EXTRACT(MONTH FROM gebdatum) = ? ", array($maand), null, 'EXTRACT(DAY FROM gebdatum)');
	}

	public static function getKomende($aantal = 10) {
        return ProfielModel::instance()->find("status IN ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND NOT gebdatum = '0000-00-00'", array(), null, "DATE_ADD(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				) ASC", $aantal);
	}

	public static function getTussen($van, $tot, $limiet = null) {
		$vanjaar = date('Y', $van);
		$totjaar = date('Y', $tot);
		$van = date('Y-m-d', $van);
		$tot = date('Y-m-d', $tot);

        return ProfielModel::instance()->find("status IN ('S_LID', 'S_GASTLID', 'S_NOVIET', 'S_KRINGEL') AND NOT gebdatum = '0000-00-00' AND (
            (CONCAT(?, SUBSTRING(gebdatum, 5)) >= ? AND CONCAT(?, SUBSTRING(gebdatum, 5)) < ?)
            OR
            (CONCAT(?, SUBSTRING(gebdatum, 5)) >= ? AND CONCAT(?, SUBSTRING(gebdatum, 5)) < ?)
        )", array($vanjaar, $van, $vanjaar, $tot, $totjaar, $van, $totjaar, $tot), null, "DATE_ADD(
					gebdatum,
					INTERVAL TIMESTAMPDIFF(
						year,
						ADDDATE(gebdatum, INTERVAL 1 DAY),
						CURRENT_DATE
					)+1 YEAR
				)", $limiet);
	}
}
