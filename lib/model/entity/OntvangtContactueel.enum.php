<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * OntvangtContactueel.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class OntvangtContactueel implements PersistentEnum {

	const JA = 'ja';
	const DIGITAAL = 'digitaal';
	const NEE = 'nee';

	public static function getTypeOptions() {
		return array(self::JA, self::DIGITAAL, self::NEE);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::JA: return 'ja';
			case self::DIGITAAL: return 'ja, digitaal';
			case self::NEE: return 'nee';
			default: throw new Exception('OntvangtContactueel onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::JA: return 'J';
			case self::DIGITAAL: return 'D';
			case self::NEE: return '-';
			default: throw new Exception('OntvangtContactueel onbekend');
		}
	}

}
