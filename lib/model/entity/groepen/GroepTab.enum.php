<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * GroepTab.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De verschillende tabbladen om een groep weer te geven.
 * 
 */
abstract class GroepTab implements PersistentEnum {

	const LIJST = 'lijst';
	const PASFOTOS = 'pasfotos';
	const STATS = 'stats';
    const EMAILS = 'emails';
	const EETWENS = 'eetwens';

	public static function getTypeOptions() {
		return array(self::LIJST, self::PASFOTOS, self::STATS, self::EMAILS, self::EETWENS);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::LIJST: return 'Lijst';
			case self::PASFOTOS: return 'Pasfoto\'s';
			case self::STATS: return 'Statistiek';
			case self::EMAILS: return 'E-mails';
			case self::EETWENS: return 'Allergie/dieet';
			default: throw new Exception('GroepTab onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::LIJST: return 'l';
			case self::PASFOTOS: return 'p';
			case self::STATS: return 's';
			case self::EMAILS: return 'e';
			case self::EETWENS: return 'a';
			default: throw new Exception('GroepTab onbekend');
		}
	}

}
