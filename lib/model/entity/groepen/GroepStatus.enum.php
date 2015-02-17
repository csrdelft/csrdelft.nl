<?php

/**
 * GroepStatus.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De status van een groep of lid in een groep.
 * 
 */
abstract class GroepStatus implements PersistentEnum {

	const FT = 'ft';
	const HT = 'ht';
	const OT = 'ot';

	public static function getTypeOptions() {
		return array(self::FT, self::HT, self::OT);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::FT: return 'future tempore'; // 'in de toekomstige tijd'
			case self::HT: return 'hoc tempore'; // 'in de huidige tijd' (lett. 'in deze tijd') 
			case self::OT: return 'olim tempore'; // 'in de verleden tijd' (lett. 'uit de tijd')
			default: throw new Exception('GroepStatus onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::FT: return 'f.t.';
			case self::HT: return 'h.t.';
			case self::OT: return 'o.t.';
			default: throw new Exception('GroepStatus onbekend');
		}
	}

}
