<?php

/**
 * KetzerSelectorSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De keuzesoort van een selector: AND (Multiple) / XOR (Single)
 * 
 */
abstract class KetzerSelectorSoort extends PersistentEnum {

	const Single = 'XOR';
	const Multiple = 'AND';

	public static function values() {
		return array(self::Single, self::Multiple);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Single: return 'Keuzerondje';
			case self::Multiple: return 'Vinkje';
			default: throw new Exception('KetzerSelectorSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Single:
			case self::Multiple:
				return $option;
			default: throw new Exception('KetzerSelectorSoort onbekend');
		}
	}

}
