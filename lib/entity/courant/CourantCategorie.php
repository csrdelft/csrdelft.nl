<?php


namespace CsrDelft\entity\courant;


use CsrDelft\common\Enum;

class CourantCategorie extends Enum {
	const VOORWOORD = 'voorwoord';
	const BESTUUR = 'bestuur';
	const CSR = 'csr';
	const OVERIG = 'overig';
	const SPONSOR = 'sponsor';

	protected static $mapChoiceToChar = [
		self::VOORWOORD => 'v',
		self::BESTUUR => 'b',
		self::CSR => 'c',
		self::OVERIG => 'o',
		self::SPONSOR => 's',
	];

	protected static $mapChoiceToDescription = [
		self::VOORWOORD => 'Voorwoord',
		self::BESTUUR => 'Bestuur',
		self::CSR => 'C.S.R.',
		self::OVERIG => 'Overig',
		self::SPONSOR => 'Sponsor',
	];

	public static function getSelectOptions() {
		return static::$mapChoiceToDescription;
	}

	public function VOORWOORD() {
		return static::from(self::VOORWOORD);
	}

	public function BESTUUR() {
		return static::from(self::BESTUUR);
	}

	public function CSR() {
		return static::from(self::CSR);
	}

	public function OVERIG() {
		return static::from(self::OVERIG);
	}

	public function SPONSOR() {
		return static::from(self::SPONSOR);
	}
}
