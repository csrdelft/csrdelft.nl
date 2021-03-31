<?php


namespace CsrDelft\common\Security;


class OAuth2Scope
{
	const PROFIEL_EMAIL = "PROFIEL:EMAIL";
	const ROLE_PROFIEL_EMAIL = "ROLE_OAUTH2_PROFIEL:EMAIL";

	const BESCHRIJVING = [
		self::PROFIEL_EMAIL => 'Lezen van primair emailadres',
	];

	public static function getBeschrijving($scope) {
		if (isset(self::BESCHRIJVING[$scope])) {
			return self::BESCHRIJVING[$scope];
		}

		return $scope;
	}
}
