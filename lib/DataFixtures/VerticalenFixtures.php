<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;

class VerticalenFixtures extends Fixture
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
            $verticale->maker = $manager->find(Profiel::class, '2020');

            $manager->persist($verticale);
        }

        $manager->flush();
    }
}
