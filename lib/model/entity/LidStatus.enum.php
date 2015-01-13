<?php

/**
 * LidStatus.enum.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class LidStatus implements PersistentEnum {

	// h.t. leden
	const Noviet = 'S_NOVIET';
	const Lid = 'S_LID';
	const Gastlid = 'S_GASTLID';
	// o.t. leden
	const Oudlid = 'S_OUDLID';
	const Erelid = 'S_ERELID';
	// niet-leden
	const Overleden = 'S_OVERLEDEN';
	const Exlid = 'S_EXLID';
	const Nobody = 'S_NOBODY';
	const Commissie = 'S_CIE';
	const Kringel = 'S_KRINGEL';

	public static function getTypeOptions() {
		return array(self::Noviet, self::Lid, self::Gastlid, self::Oudlid, self::Erelid, self::Overleden, self::Exlid, self::Nobody, self::Commissie, self::Kringel);
	}

	public static function isLid($status) {
		return $status === self::Noviet OR $status === self::Lid or $status === LidStatus::Gastlid;
	}

	public static function isOudlid($status) {
		return $status === self::Oudlid OR $status === self::Erelid;
	}

	public static function getDescription($status) {
		switch ($status) {
			case self::Noviet: return 'Noviet';
			case self::Lid: return 'Lid';
			case self::Gastlid: return 'Gastlid';
			case self::Oudlid: return 'Oudlid';
			case self::Erelid: return 'Erelid';
			case self::Overleden: return 'Overleden';
			case self::Exlid: return 'Ex-lid';
			case self::Nobody: return 'Nobody';
			case self::Commissie: return 'Commissie (LDAP)';
			case self::Kringel: return 'Kringel';
			default: throw new Exception('Ongeldige LidStatus');
		}
	}

	/**
	 * Geef een karakter terug om de status van het huidige lid aan te
	 * duiden. In de loop der tijd zijn ~ voor kringel en • voor oudlid
	 * ingeburgerd. Handig om in leden snel te zien om wat voor soort
	 * lid het gaat.
	 */
	public static function getChar($status) {
		switch ($status) {
			case self::Noviet:
			case self::Lid:
			case self::Gastlid: return '';
			case self::Commissie: return '∈';
			case self::Exlid:
			case self::Nobody: return '∉';
			case self::Kringel: return '~';
			case self::Oudlid: return '•';
			case self::Erelid: return '☀';
			case self::Overleden: return '✝';
			default: throw new Exception('Ongeldige LidStatus');
		}
	}

}
