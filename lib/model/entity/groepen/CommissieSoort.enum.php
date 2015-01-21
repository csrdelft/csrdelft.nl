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

	const BestuursCommissie = 'b';
	const Commissie = 'c';
	const SjaarCie = 's';

	public static function getTypeOptions() {
		return array(self::BestuursCommissie, self::Commissie, self::SjaarCie);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::BestuursCommissie: return 'Bestuurscommissie';
			case self::Commissie: return 'Commissie';
			case self::SjaarCie: return 'SjaarCie';
			default: throw new Exception('CommissieSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::BestuursCommissie:
			case self::Commissie:
			case self::SjaarCie:
				return $option;
			default: throw new Exception('CommissieSoort onbekend');
		}
	}

}
