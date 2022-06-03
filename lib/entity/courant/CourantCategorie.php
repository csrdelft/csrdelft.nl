<?php


namespace CsrDelft\entity\courant;


use CsrDelft\common\Enum;

/**
 * Class CourantCategorie
 * @package CsrDelft\entity\courant
 * @method static static VOORWOORD
 * @method static static CSR
 * @method static static OVERIG
 * @method static static SPONSOR
 */
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
}
