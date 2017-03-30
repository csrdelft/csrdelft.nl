<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * CommissieSoort.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * (Bestuurs-)Commissie / SjaarCie.
 * 
 */
abstract class CommissieSoort implements PersistentEnum {

	const COMMISSIE = 'c';
	const SJAARCIE = 's';
	const BESTUURSCOMMISSIE = 'b';
	const EXTERN = 'e';

	public static function getTypeOptions() {
		return array(self::COMMISSIE, self::SJAARCIE, self::BESTUURSCOMMISSIE, self::EXTERN);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::COMMISSIE: return 'Commissie';
			case self::SJAARCIE: return 'SjaarCie';
			case self::BESTUURSCOMMISSIE: return 'Bestuurscommissie';
			case self::EXTERN: return 'Externe commissie';
			default: throw new Exception('CommissieSoort onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::COMMISSIE:
			case self::SJAARCIE:
			case self::BESTUURSCOMMISSIE:
			case self::EXTERN:
				return ucfirst($option);
			default: throw new Exception('CommissieSoort onbekend');
		}
	}

}
