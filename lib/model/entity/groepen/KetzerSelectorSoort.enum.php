<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * KetzerSelectorSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De keuzesoort van een selector: AND (Multiple) / XOR (Single)
 * 
 */
abstract class KetzerSelectorSoort implements PersistentEnum {

	const SINGLE = 'XOR';
	const MULTIPLE = 'AND';

	public static function values() {
		return array(self::SINGLE, self::MULTIPLE);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::SINGLE: return 'Keuzerondje';
			case self::MULTIPLE: return 'Vinkje';
			default: throw new Exception('KetzerSelectorSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::SINGLE:
			case self::MULTIPLE:
				return $option;
			default: throw new Exception('KetzerSelectorSoort onbekend');
		}
	}

}
