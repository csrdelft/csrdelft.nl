<?php

/**
 * Gang.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Gang van gerecht.
 * Drank apart.
 * 
 */
abstract class HappieGang implements PersistentEnum {

	const Drank = 'd';
	const Voorgerecht = 'v';
	const Hoofdgerecht = 'h';
	const Bijgerecht = 'b';
	const Nagerecht = 'n';

	public static function getTypeOptions() {
		return array(self::Drank, self::Voorgerecht, self::Hoofdgerecht, self::Bijgerecht, self::Nagerecht);
	}

}
