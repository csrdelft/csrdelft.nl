<?php

/**
 * GesprekStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class GesprekStatus implements PersistentEnum {

	const Actief = 'a';
	const Online = 'o';
	const Offline = 'f';

	public static function getTypeOptions() {
		return array(self::Actief, self::Online, self::Offline);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Actief: return 'Actief';
			case self::Online: return 'Online';
			case self::Offline: return 'Offline';
			default: throw new Exception('GesprekStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Actief: return '☀';
			case self::Online: return '•';
			case self::Offline: return '';
			default: throw new Exception('GesprekStatus onbekend');
		}
	}

}
