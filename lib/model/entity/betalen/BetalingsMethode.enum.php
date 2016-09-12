<?php

/**
 * BetalingsMethode.enum.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class BetalingsMethode implements PersistentEnum {

	const Contant = 'c';
	const PIN = 'pin';
	const Machtiging = 'm';
	const OvermakenVooraf = 'o_v';
	const OvermakenAchteraf = 'o_a';
	const CiviSaldo = 'c_s';
	const SoccieSaldo = 's_s';
	const MaalcieSaldo = 'm_s';

	public static function getTypeOptions() {
		return array(self::Contant, self::PIN, self::Machtiging, self::OvermakenVooraf, self::OvermakenAchteraf, self::CiviSaldo, self::SoccieSaldo, self::MaalcieSaldo);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Contant: return 'Contant';
			case self::PIN: return 'PIN';
			case self::Machtiging: return 'Machtiging';
			case self::OvermakenVooraf: return 'Vooraf overmaken';
			case self::OvermakenAchteraf: return 'Achteraf overmaken';
			case self::CiviSaldo: return 'CiviSaldo';
			case self::SoccieSaldo: return 'SoccieSaldo';
			case self::MaalcieSaldo: return 'MaalcieSaldo';
			default: throw new Exception('BetalingsMethode onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Contant:
			case self::PIN:
			case self::Machtiging:
			case self::OvermakenVooraf:
			case self::OvermakenAchteraf:
			case self::CiviSaldo:
			case self::SoccieSaldo:
			case self::MaalcieSaldo:
				return strtoupper($option);
			default: throw new Exception('BetalingsMethode onbekend');
		}
	}

}
