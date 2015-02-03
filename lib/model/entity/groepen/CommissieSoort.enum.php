<?php

/**
 * CommissieSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * (Bestuurs-)Commissie / SjaarCie.
 * 
 */
abstract class CommissieSoort implements PersistentEnum {

	const Commissie = 'c';
	const SjaarCie = 's';
	const BestuursCommissie = 'b';
	const Extern = 'e';

	public static function getTypeOptions() {
		return array(self::Commissie, self::SjaarCie, self::BestuursCommissie, self::Extern);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Commissie: return 'Commissie';
			case self::SjaarCie: return 'SjaarCie';
			case self::BestuursCommissie: return 'Bestuurscommissie';
			case self::Extern: return 'Externe commissie';
			default: throw new Exception('CommissieSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Commissie:
			case self::SjaarCie:
			case self::BestuursCommissie:
			case self::Extern:
				return ucfirst($option);
			default: throw new Exception('CommissieSoort onbekend');
		}
	}

}
