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

	const Nieuw = 'n';
	const Gewijzigd = 'g';
	const KeukenBezig = 'b';
	const KeukenKlaar = 'k';
	const Uitgeserveerd = 'u';

	public static function getTypeOptions() {
		return array(self::Nieuw, self::Gewijzigd, self::KeukenBezig, self::KeukenKlaar, self::Uitgeserveerd);
	}

}
