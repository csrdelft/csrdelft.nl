<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;

class ProfielFixture extends Fixture
{
	public function load(ObjectManager $manager)
	{
		$lichtingen = range(20, 29);
		$lichtingsGrootte = 50;
		$faker = Faker::create('nl_NL');

		foreach ($lichtingen as $lichting) {
			foreach (range(0, $lichtingsGrootte) as $index) {
				$lidNummer = sprintf('%02d%02d', $lichting, $index);
				$geslacht = $faker->randomElement(['male', 'female']);

				$profiel = new Profiel();
				$profiel->uid = $lidNummer;
				$profiel->lidjaar = 2000 + $lichting;
				$profiel->geslacht = [
					'male' => Geslacht::Man(),
					'female' => Geslacht::Vrouw(),
				][$geslacht];
				// TODO $profiel->changelog;
				$profiel->changelog = [
					new ProfielLogTextEntry('Aangemaakt door fixtures'),
				];
				$profiel->voornaam = $faker->firstName($geslacht);
				$profiel->voornamen =
					$profiel->voornaam . ' ' . $faker->firstName($geslacht);
				$profiel->voorletters = implode(
					'',
					array_map(function ($el) {
						return substr($el, 0, 1) . '.';
					}, explode(' ', $profiel->voornamen))
				);
				$profiel->tussenvoegsel = '';
				$profiel->achternaam = $faker->lastName;
				$profiel->postfix = '';
				$profiel->nickname = '';
				$profiel->duckname = '';
				$profiel->gebdatum = $faker->dateTimeBetween('-25 years', '-18 years');
				$profiel->sterfdatum = null;
				$profiel->lengte = $faker->numberBetween(160, 210);
				// getrouwd
				$profiel->echtgenoot = null;
				$profiel->adresseringechtpaar = null;
				$profiel->ontvangtcontactueel = OntvangtContactueel::Nee();
				// adres
				$profiel->adres = $faker->streetAddress;
				$profiel->postcode = $faker->postcode;
				$profiel->woonplaats = $faker->city;
				$profiel->land = $faker->country;
				$profiel->telefoon = $faker->phoneNumber;
				$profiel->o_adres = $faker->streetAddress;
				$profiel->o_postcode = $faker->postcode;
				$profiel->o_woonplaats = $faker->city;
				$profiel->o_land = $faker->country;
				$profiel->o_telefoon = $faker->phoneNumber;
				// contact
				$profiel->email = $faker->email;
				$profiel->sec_email = $faker->email;
				$profiel->mobiel = $faker->phoneNumber;
				$profiel->linkedin = null;
				$profiel->website = null;
				// studie
				$profiel->studie = null;
				$profiel->studiejaar = 2000 + $lichting;
				$profiel->beroep = null;
				// lidmaatschap
				$profiel->lidafdatum = null;
				$profiel->status = LidStatus::Lid;
				// geld
				$profiel->bankrekening = $faker->iban('NL');
				$profiel->machtiging = true;
				// verticale
				$profiel->moot = null;
				$profiel->verticale = $faker->randomElement([
					'A',
					'B',
					'C',
					'D',
					'E',
					'F',
					'G',
					'H',
					'I',
				]);
				$profiel->verticaleleider = false;
				$profiel->kringcoach = false;
				// civi-gegevens
				$profiel->patroon = null;
				$profiel->eetwens = null;
				$profiel->corvee_punten = 0;
				$profiel->corvee_punten_bonus = 0;
				// novitiaat
				$profiel->novitiaat = null;
				$profiel->novitiaatBijz = null;
				$profiel->medisch = null;
				$profiel->startkamp = null;
				$profiel->matrixPlek = null;
				$profiel->novietSoort = null;
				$profiel->kgb = null;
				$profiel->vrienden = null;
				$profiel->middelbareSchool = null;
				$profiel->profielOpties = null;
				// overig
				$profiel->kerk = null;
				$profiel->muziek = null;
				$profiel->zingen = null;

				$manager->persist($profiel);
			}
		}

		$manager->flush();
	}
}
