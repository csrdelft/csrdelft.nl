<?php

namespace CsrDelft\model\entity\security;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * AccessAction.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * CRUD + groepen-acties.
 */
abstract class AccessAction extends PersistentEnum {

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
	protected static $supportedChoices = [
		self::Bekijken => self::Bekijken,
		self::Aanmelden => self::Aanmelden,
		self::Bewerken => self::Bewerken,
		self::Afmelden => self::Afmelden,
		self::Opvolging => self::Opvolging,
		self::Aanmaken => self::Aanmaken,
		self::Wijzigen => self::Wijzigen,
		self::Verwijderen => self::Verwijderen,
		self::Beheren => self::Beheren,
		self::Rechten => self::Rechten,
	];

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
