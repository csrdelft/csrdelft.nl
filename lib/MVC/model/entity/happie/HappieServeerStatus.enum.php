<?php

/**
 * ServeerStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Serveer status.
 * 
 */
abstract class HappieServeerStatus implements PersistentEnum {

	const Nieuw = 'nieuw';
	const Gewijzigd = 'gewijzigd';
	const KeukenBezig = 'keukenbezig';
	const KeukenKlaar = 'keukenklaar';
	const Uitgeserveerd = 'uitgeserveerd';

	public static function getTypeOptions() {
		return array(self::Nieuw, self::Gewijzigd, self::KeukenBezig, self::KeukenKlaar, self::Uitgeserveerd);
	}

}
