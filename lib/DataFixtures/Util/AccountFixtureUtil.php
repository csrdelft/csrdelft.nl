<?php

namespace CsrDelft\DataFixtures\Util;

use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use Faker\Generator;
use Symfony\Component\Uid\Uuid;

class AccountFixtureUtil
{
	public static function maakAccount(
		Generator $faker,
		$profiel,
		$accessRole = null
	): Account {
		$account = new Account();
		$account->uuid = Uuid::v4();
		$account->username = '';
		$account->email = $faker->email;
		$account->pass_hash = '';
		$account->failed_login_attempts = 0;
		$account->pass_since = date_create_immutable();
		$account->uid = $profiel->uid;
		$account->profiel = $profiel;
		$account->perm_role = $accessRole ?? AccessRole::Lid;

		return $account;
	}
}
