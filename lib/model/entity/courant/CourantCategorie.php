<?php


namespace CsrDelft\model\entity\courant;


use CsrDelft\Orm\Entity\PersistentEnum;

class CourantCategorie extends PersistentEnum {
	const VOORWOORD = 'voorwoord';
	const BESTUUR = 'bestuur';
	const CSR = 'csr';
	const OVERIG = 'overig';
	const SPONSOR = 'sponsor';

	protected static $supportedChoices = [
		self::VOORWOORD => self::VOORWOORD,
		self::BESTUUR => self::BESTUUR,
		self::CSR => self::CSR,
		self::OVERIG => self::OVERIG,
		self::SPONSOR => self::SPONSOR,
	];

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
}
