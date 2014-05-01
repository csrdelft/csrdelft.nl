<?php

/**
 * KetzerSelectorSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De keuzesoort van een selector: AND / XOR
 * 
 */
final class KetzerSelectorSoort extends PersistentEnum {

	const Checkbox = 'AND';
	const Radio = 'XOR';

	public static function values() {
		return array(self::Checkbox, self::Radio);
	}

}
