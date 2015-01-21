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

	public static function getDescription($option) {
		switch ($option) {
			case self::Ja: return 'ja';
			case self::Digitaal: return 'ja, digitaal';
			case self::Nee: return 'nee';
			default: throw new Exception('OntvangtContactueel onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Ja: return 'J';
			case self::Digitaal: return 'D';
			case self::Nee: return '-';
			default: throw new Exception('OntvangtContactueel onbekend');
		}
	}

}
