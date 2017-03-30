<?php
use CsrDelft\Orm\Entity\PersistentEnum;

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

	const NOBODY = 'R_NOBODY';
	const ETER = 'R_ETER';
	const OUDLID = 'R_OUDLID';
	const LID = 'R_LID';
	const BASFCIE = 'R_BASF';
	const MAALCIE = 'R_MAALCIE';
	const BESTUUR = 'R_BESTUUR';
	const PUBCIE = 'R_PUBCIE';

	/**
	 * Extra rechtenset voor Am. de Vlieger.
	 * Een combinatie van BASFCie (archief) en MaalCie.
	 */
	const Vlieger = "R_VLIEGER";

	public static function getTypeOptions() {
		return array(self::NOBODY, self::ETER, self::OUDLID, self::LID,
			self::BASFCIE, self::MAALCIE, self::BESTUUR, self::PUBCIE, self::Vlieger);
	}

	public static function canChangeAccessRoleTo($from) {
		switch ($from) {
			case self::PUBCIE: return self::getTypeOptions();
			case self::BESTUUR: return array(self::NOBODY, self::ETER, self::OUDLID, self::LID);
			default: return array();
		}
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::NOBODY: return 'Ex-lid/Nobody';
			case self::ETER: return 'Eter (inlog voor abo\'s)';
			case self::OUDLID: return 'Oudlid';
			case self::LID: return 'Lid';
			case self::BASFCIE: return 'BASFCie-rechten';
			case self::MAALCIE: return 'MaalCie-rechten';
			case self::BESTUUR: return 'Bestuur-rechten';
			case self::PUBCIE: return 'PubCie-rechten';
			case self::Vlieger: return 'Vlieger-rechten';
			default: throw new Exception('AccessRole onbekend');
		}
	}

	public static function getChar($option) {
		switch ($option) {
			case self::NOBODY: return 'N';
			case self::ETER: return 'E';
			case self::OUDLID: return 'O';
			case self::LID: return 'L';
			case self::BASFCIE: return 'BASF';
			case self::MAALCIE: return 'M';
			case self::BESTUUR: return 'B';
			case self::PUBCIE: return 'P';
			case self::Vlieger: return 'V';
			default: throw new Exception('AccessRole onbekend');
		}
	}

}
