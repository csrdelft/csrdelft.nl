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
	const VoormaligOndervereniging = 'v';

	public static function getTypeOptions() {
		return array(self::Ondervereniging, self::AdspirantOndervereniging, self::VoormaligOndervereniging);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Ondervereniging: return 'ondervereniging';
			case self::AdspirantOndervereniging: return 'adspirant-ondervereniging';
			case self::VoormaligOndervereniging: return 'voormalig ondervereniging';
			default: throw new Exception('OnderverenigingStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Ondervereniging:
			case self::AdspirantOndervereniging:
			case self::VoormaligOndervereniging:
				return $option;
			default: throw new Exception('OnderverenigingStatus onbekend');
		}
	}

}
