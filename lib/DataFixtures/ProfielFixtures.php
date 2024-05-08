<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\DataFixtures\Util\FixtureHelpers;
use CsrDelft\DataFixtures\Util\ProfielFixtureUtil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;
use Faker\Generator;

/**
 * Genereer een lading lichtingen om de stek te vullen met wat data.
 * Deze profielen hebben geen account en kunnen dus ook niet inloggen.
 */
class ProfielFixtures extends Fixture
{
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
		$lichtingen = range(
			FixtureHelpers::LIDJAAR_START,
			FixtureHelpers::LIDJAAR_EIND
		);
		foreach ($lichtingen as $lichting) {
			foreach (range(0, FixtureHelpers::LICHTING_GROOTTE) as $index) {
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
