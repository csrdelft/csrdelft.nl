<?php

namespace CsrDelft\DataFixtures\Util;

use Faker\Factory as Faker;

class FixtureHelpers
{
	public static function getUid()
	{
		$faker = Faker::create('nl_NL');

		$lichting = $faker->numberBetween(20, 29);
		$id = $faker->numberBetween(0, 50);

		return sprintf('%02d%02d', $lichting, $id);
	}
}
