<?php

/**
 * KetzerSelectorSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De keuzesoort van een selector: AND (Multiple) / XOR (Single)
 * 
 */
final class KetzerSelectorSoort extends PersistentEnum {

	const Single = 'XOR';
	const Multiple = 'AND';

	public static function values() {
		return array(self::Single, self::Multiple);
	}

}
