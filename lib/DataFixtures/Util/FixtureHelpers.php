<?php

namespace CsrDelft\DataFixtures\Util;

use Faker\Factory as Faker;

class FixtureHelpers
{
	const LIDJAAR_START = 20;
	const LIDJAAR_EIND = 29;
	const LICHTING_GROOTTE = 50;

	public static function getUid(): string
	{
		$faker = Faker::create('nl_NL');

		$lichting = $faker->numberBetween(self::LIDJAAR_START, self::LIDJAAR_EIND);
		$id = $faker->numberBetween(0, self::LICHTING_GROOTTE);

		return sprintf('%02d%02d', $lichting, $id);
	}

	public static function getRandomUids($aantal): array
	{
		$uids = [];

		while ($aantal > count($uids)) {
			$randomUid = static::getUid();
			if (!in_array($randomUid, $uids)) {
				$uids[] = $randomUid;
			}
		}
		return $uids;
	}
}
