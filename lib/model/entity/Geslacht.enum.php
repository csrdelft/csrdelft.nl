<?php

/**
 * Geslacht.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class Geslacht implements PersistentEnum {

	const Man = 'm';
	const Vrouw = 'v';

	public static function getTypeOptions() {
		return array(self::Man, self::Vrouw);
	}

}
