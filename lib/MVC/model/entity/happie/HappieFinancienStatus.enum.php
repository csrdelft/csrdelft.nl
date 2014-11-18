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

	const Nieuw = 'nieuw';
	const Gewijzigd = 'gewijzigd';
	const Verwerkt = 'verwerkt';

	public static function getTypeOptions() {
		return array(self::Nieuw, self::Gewijzigd, self::Verwerkt);
	}

}
