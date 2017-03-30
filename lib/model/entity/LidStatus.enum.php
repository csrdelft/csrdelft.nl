<?php
use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * LidStatus.enum.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class LidStatus implements PersistentEnum {

	// h.t. leden
	const NOVIET = 'S_NOVIET';
	const LID = 'S_LID';
	const GASTLID = 'S_GASTLID';
	// o.t. leden
	const OUDLID = 'S_OUDLID';
	const ERELID = 'S_ERELID';
	// niet-leden
	const OVERLEDEN = 'S_OVERLEDEN';
	const EXLID = 'S_EXLID';
	const NOBODY = 'S_NOBODY';
	const COMMISSIE = 'S_CIE';
	const KRINGEL = 'S_KRINGEL';

	public static $lidlike = array(self::NOVIET, self::LID, self::GASTLID);
	public static $oudlidlike = array(self::OUDLID, self::ERELID);

	public static function getTypeOptions() {
		return array(self::NOVIET, self::LID, self::GASTLID, self::OUDLID, self::ERELID, self::OVERLEDEN, self::EXLID, self::NOBODY, self::COMMISSIE, self::KRINGEL);
	}

	public static function isLidLike($option) {
		return in_array($option, self::$lidlike);
	}

	public static function isOudlidLike($option) {
		return in_array($option, self::$oudlidlike);
	}

	public static function getDescription($option) {
		switch ($option) {
			case self::NOVIET: return 'Noviet';
			case self::LID: return 'Lid';
			case self::GASTLID: return 'Gastlid';
			case self::OUDLID: return 'Oudlid';
			case self::ERELID: return 'Erelid';
			case self::OVERLEDEN: return 'Overleden';
			case self::EXLID: return 'Ex-lid';
			case self::NOBODY: return 'Nobody';
			case self::COMMISSIE: return 'Commissie (LDAP)';
			case self::KRINGEL: return 'Kringel';
			default: throw new Exception('LidStatus onbekend');
		}
	}

	/**
	 * Geef een karakter terug om de status van het huidige lid aan te
	 * duiden. In de loop der tijd zijn ~ voor kringel en • voor oudlid
	 * ingeburgerd. Handig om in leden snel te zien om wat voor soort
	 * lid het gaat.
	 */
	public static function getChar($option) {
		switch ($option) {
			case self::NOVIET:
			case self::LID:
			case self::GASTLID: return '';
			case self::COMMISSIE: return '∈';
			case self::EXLID:
			case self::NOBODY: return '∉';
			case self::KRINGEL: return '~';
			case self::OUDLID: return '•';
			case self::ERELID: return '☀';
			case self::OVERLEDEN: return '✝';
			default: throw new Exception('LidStatus onbekend');
		}
	}

}
