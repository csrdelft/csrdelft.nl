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

	const Nieuw = 'Nieuw';
	const Gewijzigd = 'Gewijzigd';
	const KeukenBezig = 'Keuken Bezig';
	const KeukenKlaar = 'Keuken Klaar';
	const Uitgeserveerd = 'Uitgeserveerd';

	public static function getTypeOptions() {
		return array(self::Nieuw, self::Gewijzigd, self::KeukenBezig, self::KeukenKlaar, self::Uitgeserveerd);
	}

}
