<?php


namespace CsrDelft\common\Security;


class OAuth2Scope
{
	const PROFIEL_EMAIL = "PROFIEL:EMAIL";
	const BAR_NORMAAL = "BAR:NORMAAL";
	const BAR_BEHEER = "BAR:BEHEER";

	const BESCHRIJVING = [
		self::PROFIEL_EMAIL => 'Lezen van primair emailadres',
		self::BAR_NORMAAL => 'Barsysteem gebruiken',
		self::BAR_BEHEER => 'Barsysteem beheren'
	];

	public static function getBeschrijving($scope) {
		if (isset(self::BESCHRIJVING[$scope])) {
			return self::BESCHRIJVING[$scope];
		}

		return $scope;
	}
}
