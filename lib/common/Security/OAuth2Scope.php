<?php

namespace CsrDelft\common\Security;

use CsrDelft\common\CsrException;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;

/**
 * Houdt in sync met config/packages/league_oauth2_server.yaml
 */
class OAuth2Scope
{
	const STANDAARD = 'STANDAARD';
	const PROFIEL_EMAIL = 'PROFIEL:EMAIL';
	const BAR_NORMAAL = 'BAR:NORMAAL';
	const BAR_BEHEER = 'BAR:BEHEER';
	const BAR_TRUST = 'BAR:TRUST';
	const WIKI_BESTUUR = 'WIKI:BESTUUR';

	const BESCHRIJVING = [
		self::STANDAARD =>
			'Als er niet om een scope wordt gevraagd wordt deze scope gebruikt',
		self::PROFIEL_EMAIL => 'Lezen van primair emailadres',
		self::BAR_NORMAAL => 'Het bar systeem gebruiken om drankjes te strepen.',
		self::BAR_BEHEER =>
			'Het bar systeem gebruiken om in te leggen en bijnamen aan te passen.',
		self::BAR_TRUST => 'Een bar systeem installeren.',
		self::WIKI_BESTUUR => 'Bestuurswiki lezen.',
	];

	const MAG = [
		self::STANDAARD => 'ROLE_LOGGED_IN',
		self::PROFIEL_EMAIL => 'ROLE_LOGGED_IN',
		self::BAR_NORMAAL => 'ROLE_ADMIN',
		self::BAR_BEHEER => 'ROLE_ADMIN',
		self::BAR_TRUST => 'ROLE_ADMIN',
		self::WIKI_BESTUUR => 'bestuur:ft,bestuur,bestuur:ot',
	];

	// Optionele scopes
	const OPTIONAL = [
		self::BAR_BEHEER => true,
		self::BAR_TRUST => true,
	];

	/**
	 * @param Scope|string $scope
	 * @return mixed
	 */
	public static function magScope($scope)
	{
		if (isset(self::MAG[(string) $scope])) {
			return self::MAG[(string) $scope];
		}

		throw new CsrException("Scope $scope heeft geen rechten gedefinieerd");
	}

	public static function isOptioneel($scope)
	{
		if (isset(self::OPTIONAL[(string) $scope])) {
			return true;
		}

		return false;
	}

	/**
	 * @param Scope|string $scope
	 * @return string
	 */
	public static function getBeschrijving($scope)
	{
		if (isset(self::BESCHRIJVING[(string) $scope])) {
			return self::BESCHRIJVING[(string) $scope];
		}

		throw new CsrException("Scope $scope heeft geen beschrijving");
	}
}
