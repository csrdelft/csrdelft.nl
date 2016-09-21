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
	const BASFCie = 'R_BASF';
	const MaalCie = 'R_MAALCIE';
	const Bestuur = 'R_BESTUUR';
	const PubCie = 'R_PUBCIE';

	public static function getTypeOptions() {
		return array(self::Nobody, self::Eter, self::Oudlid, self::Lid, self::BASFCie, self::MaalCie, self::Bestuur, self::PubCie);
	}

	public static function canChangeAccessRoleTo($from) {
		switch ($from) {
			case self::PubCie: return self::getTypeOptions();
			case self::Bestuur: return array(self::Nobody, self::Eter, self::Oudlid, self::Lid);
			default: return array();
		}
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::Nobody: return 'Ex-lid/Nobody';
			case self::Eter: return 'Eter (inlog voor abo\'s)';
			case self::Oudlid: return 'Oudlid';
			case self::Lid: return 'Lid';
			case self::BASFCie: return 'BASFCie-rechten';
			case self::MaalCie: return 'MaalCie-rechten';
			case self::Bestuur: return 'Bestuur-rechten';
			case self::PubCie: return 'PubCie-rechten';
			default: throw new Exception('AccessRole onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::Nobody: return 'N';
			case self::Eter: return 'E';
			case self::Oudlid: return 'O';
			case self::Lid: return 'L';
			case self::BASFCie: return 'BASF';
			case self::MaalCie: return 'M';
			case self::Bestuur: return 'B';
			case self::PubCie: return 'P';
			default: throw new Exception('AccessRole onbekend');
		}
	}

}
