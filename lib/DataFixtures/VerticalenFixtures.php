<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Verticale;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;

class VerticalenFixtures extends Fixture implements DependentFixtureInterface
{
	public function load(ObjectManager $manager)
	{
		$faker = Faker::create('nl_NL');

		$verticaleLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

		foreach ($verticaleLetters as $letter) {
			$verticale = new Verticale();

			$verticale->letter = $letter;
			$verticale->naam = ucfirst($faker->unique()->word);
			$verticale->familie = 'Verticale';
			$verticale->beginMoment = date_create_immutable();
			$verticale->eindMoment = null;
			$verticale->status = GroepStatus::HT();
			$verticale->samenvatting = '';
			$verticale->maker = $this->getReference(AccountFixtures::UID_PUBCIE);

			$manager->persist($verticale);
		}

		$manager->flush();
	}

	public function getDependencies()
	{
		return [AccountFixtures::class];
	}
}
