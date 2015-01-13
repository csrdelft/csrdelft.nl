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
		return (int) Database::sqlSelect('MIN(lidjaar)', 'profielen', 'lidjaar > 0')->fetchColumn();
	}

}
