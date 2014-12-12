<?php

/**
 * GroepTab.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De verschillende tabbladen om een groep weer te geven.
 * 
 */
abstract class GroepTab implements PersistentEnum {

	const Lijst = 'lijst';
	const Pasfotos = 'pasfotos';
	const Statistiek = 'stats';
	const Emails = 'emails';

	public static function getTypeOptions() {
		return array(self::Lijst, self::Pasfotos, self::Statistiek, self::Emails);
	}

}
