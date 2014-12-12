<?php

/**
 * HuisStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De status van een huis / woonoord.
 * 
 */
abstract class HuisStatus implements PersistentEnum {

	const Woonoord = 'woonoord';
	const Huis = 'huis';

	public static function getTypeOptions() {
		return array(self::Woonoord, self::Huis);
	}

}
