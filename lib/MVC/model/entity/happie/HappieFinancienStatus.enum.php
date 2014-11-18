<?php

/**
 * FinancienStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Financiele status.
 * 
 */
abstract class HappieFinancienStatus implements PersistentEnum {

	const Nieuw = 'n';
	const Gewijzigd = 'g';
	const Verwerkt = 'v';

	public static function getTypeOptions() {
		return array(self::Nieuw, self::Gewijzigd, self::Verwerkt);
	}

}
