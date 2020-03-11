<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;

class VerticalenFixtures extends Fixture {
	public function load(ObjectManager $manager) {
		$faker = Faker::create('nl_NL');

		$verticaleLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
		$verticaleNamen = $faker->words(count($verticaleLetters));

		foreach ($verticaleLetters as $i => $letter) {
			$verticale = new Verticale();

			$verticale->letter = $letter;
			$verticale->naam = ucfirst($verticaleNamen[$i]);
			$verticale->familie = 'Verticale';
			$verticale->begin_moment = date_create_immutable();
			$verticale->eind_moment = null;
			$verticale->status = GroepStatus::HT();
			$verticale->samenvatting = '';
			$verticale->maker = $manager->find(Profiel::class, '2020');

			$manager->persist($verticale);
		}

		$manager->flush();
	}
}
