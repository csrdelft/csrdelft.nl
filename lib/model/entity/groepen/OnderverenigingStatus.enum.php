<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * OnderverenigingStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De status van een ondervereniging.
 * 
 */
abstract class OnderverenigingStatus implements PersistentEnum {

	const ADSPIRANTONDERVERENIGING = 'a';
	const ONDERVERENIGING = 'o';
	const VOORMALIGONDERVERENIGING = 'v';

	public static function getTypeOptions() {
		return array(self::ADSPIRANTONDERVERENIGING, self::ONDERVERENIGING, self::VOORMALIGONDERVERENIGING);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::ADSPIRANTONDERVERENIGING: return 'adspirant-ondervereniging';
			case self::ONDERVERENIGING: return 'ondervereniging';
			case self::VOORMALIGONDERVERENIGING: return 'voormalig ondervereniging';
			default: throw new Exception('OnderverenigingStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::ADSPIRANTONDERVERENIGING:
			case self::ONDERVERENIGING:
			case self::VOORMALIGONDERVERENIGING:
				return ucfirst($option);
			default: throw new Exception('OnderverenigingStatus onbekend');
		}
	}

}
