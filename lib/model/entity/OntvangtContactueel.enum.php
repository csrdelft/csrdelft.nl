<?php

/**
 * OntvangtContactueel.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class OntvangtContactueel implements PersistentEnum {

	const Ja = 'ja';
	const Digitaal = 'digitaal';
	const Nee = 'nee';

	public static function getTypeOptions() {
		return array(self::Ja, self::Digitaal, self::Nee);
	}

}
