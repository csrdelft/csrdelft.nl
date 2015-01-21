<?php

/**
 * OnderverenigingStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De status van een ondervereniging.
 * 
 */
abstract class OnderverenigingStatus implements PersistentEnum {

	const Ondervereniging = 'o';
	const AdspirantOndervereniging = 'a';

	public static function getTypeOptions() {
		return array(self::Ondervereniging, self::AdspirantOndervereniging);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Ondervereniging: return 'ondervereniging';
			case self::AdspirantOndervereniging: return 'adspirant-ondervereniging';
			default: throw new Exception('OnderverenigingStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Ondervereniging:
			case self::AdspirantOndervereniging:
				return $option;
			default: throw new Exception('OnderverenigingStatus onbekend');
		}
	}

}
