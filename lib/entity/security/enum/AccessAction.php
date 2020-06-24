<?php

namespace CsrDelft\entity\security\enum;

use CsrDelft\common\Enum;

/**
 * AccessAction.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * CRUD + groepen-acties.
 */
class AccessAction extends Enum {

	// lezen
	/**
	 * Leesrechten
	 */
	const Bekijken = 'r'; // retrieve

	/**
	 * Schrijfrechten (groepen)
	 */
	const Aanmelden = 'j'; // join
	const Bewerken = 'e'; // edit
	const Afmelden = 'l'; // leave
	const Opvolging = 's'; // sequence

	/**
	 * Schrijfrechten (algemeen)
	 */
	const Aanmaken = 'c'; // create
	const Wijzigen = 'u'; // update
	const Verwijderen = 'd'; // delete

	/**
	 * Beheerrechten
	 */
	const Beheren = 'm'; // manage
	const Rechten = 'p'; // permissions\

	/**
	 * @var string[]
	 */
	protected static $mapChoiceToDescription = [
		self::Bekijken => 'Bekijken',
		self::Aanmelden => 'Aanmelden',
		self::Bewerken => 'Aanmelding bewerken',
		self::Afmelden => 'Afmelden',
		self::Opvolging => 'Opvolging aanpassen',
		self::Aanmaken => 'Nieuwe aanmaken',
		self::Wijzigen => 'Wijzigen',
		self::Verwijderen => 'Verwijderen',
		self::Beheren => 'Beheren',
		self::Rechten => 'Rechten instellen',
	];
}
