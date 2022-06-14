<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\DataFixtures\Util\ProfielFixtureUtil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Faker\Generator;

class ProfielFixture extends Fixture
{
	const LICHTING_GROOTTE = 50;

	/**
	 * @var Generator
	 */
	private $faker;

	public function __construct()
	{
		$this->faker = Faker::create('nl_NL');
	}

	public function load(ObjectManager $manager)
	{
		$lichtingen = range(20, 29);
		foreach ($lichtingen as $lichting) {
			foreach (range(0, self::LICHTING_GROOTTE) as $index) {
				$lidNummer = sprintf('%02d%02d', $lichting, $index);
				$manager->persist(
					ProfielFixtureUtil::maakProfiel(
						$this->faker,
						$lidNummer,
						null,
						null,
						null,
						''
					)
				);
			}
		}

		$manager->flush();
	}
}
