<?php

/**
 * AccessRole.enum.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * RBAC MAC roles.
 * 
 * @see AccessModel
 */
abstract class AccessRole implements PersistentEnum {

	const Nobody = 'R_NOBODY';
	const Eter = 'R_ETER';
	const Oudlid = 'R_OUDLID';
	const Lid = 'R_LID';
	const Basfcie = 'R_BASF';
	const Maalcie = 'R_MAALCIE';
	const Bestuur = 'R_BESTUUR';
	const Pubcie = 'R_PUBCIE';

	public static function getTypeOptions() {
		return array(self::Nobody, self::Eter, self::Oudlid, self::Lid, self::Basfcie, self::Maalcie, self::Bestuur, self::Pubcie);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Nobody: return 'Ex-lid/Nobody';
			case self::Eter: return 'Eter (inlog voor abo\'s)';
			case self::Oudlid: return 'Oudlid';
			case self::Lid: return 'Lid';
			case self::Basfcie: return 'BASFcie-rechten';
			case self::Maalcie: return 'Maalcie-rechten';
			case self::Bestuur: return 'Bestuur-rechten';
			case self::Pubcie: return 'Pubcie-rechten';
			default: throw new Exception('AccessRole onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Nobody: return 'N';
			case self::Eter: return 'E';
			case self::Oudlid: return 'O';
			case self::Lid: return 'L';
			case self::Basfcie: return 'BASF';
			case self::Maalcie: return 'M';
			case self::Bestuur: return 'B';
			case self::Pubcie: return 'P';
			default: throw new Exception('AccessRole onbekend');
		}
	}

}
