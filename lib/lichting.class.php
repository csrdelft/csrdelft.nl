<?php

class Lichting {

	public static function getHuidigeJaargang() {
		$jaargang = self::getJongsteLichting();
		return $jaargang . '-' . ($jaargang + 1);
	}

	public static function getJongsteLichting() {
		$lichting = (int) date('Y');
		if (date('m') < 9) { // nieuwe lichting in september
			$lichting--;
		}
		return (int) $lichting;
	}

	public static function getOudsteLichting() {
		$db = MijnSqli::instance();
		$query = "
			SELECT MIN(lidjaar) as oud
			FROM lid
			WHERE lidjaar>0";
		$result = $db->query($query);
		while ($row = $db->next($result)) {
			return (int) $row['oud'];
		}
	}

}
