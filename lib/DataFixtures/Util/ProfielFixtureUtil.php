<?php

namespace CsrDelft\DataFixtures\Util;

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\OntvangtContactueel;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\profiel\ProfielLogTextEntry;
use DateTimeImmutable;
use Faker\Generator;

class ProfielFixtureUtil
{
	/**
	 * @param Generator $faker
	 * @param $uid
	 * @param string|null $nickname
	 * @param string|null $voornaam
	 * @param string|null $achternaam
	 * @param string $voorletters
	 * @return Profiel
	 */
	public static function maakProfiel(
		Generator $faker,
		$uid,
		string $nickname = null,
		string $voornaam = null,
		string $achternaam = null,
		string $voorletters = ''
	): Profiel {
		$geslacht = $faker->randomElement(['male', 'female']);

		$profiel = new Profiel();
		$profiel->uid = $uid;
		$profiel->lidjaar = (int) ('20' . substr((string) $uid, 2));
		$profiel->geslacht = [
			'male' => Geslacht::Man(),
			'female' => Geslacht::Vrouw(),
		][$geslacht];
		// TODO $profiel->changelog;
		$profiel->changelog = [new ProfielLogTextEntry('Aangemaakt door fixtures')];
		$profiel->voornaam = $voornaam ?? $faker->firstName($geslacht);
		$profiel->voornamen =
			$profiel->voornaam . ' ' . $faker->firstName($geslacht);
		$profiel->voorletters =
			$voorletters ??
			implode(
				'',
				array_map(
					fn($el) => substr((string) $el, 0, 1) . '.',
					explode(' ', $profiel->voornamen)
				)
			);
		$profiel->tussenvoegsel = '';
		$profiel->achternaam = $achternaam ?? $faker->lastName();
		$profiel->postfix = '';
		$profiel->nickname = $nickname ?? '';
		$profiel->duckname = '';
		$profiel->gebdatum = DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-25 years', '-18 years'));
		$profiel->sterfdatum = null;
		$profiel->lengte = $faker->numberBetween(160, 210);
		// getrouwd
		$profiel->echtgenoot = null;
		$profiel->adresseringechtpaar = null;
		$profiel->ontvangtcontactueel = OntvangtContactueel::Nee();
		// adres
		$profiel->adres = $faker->streetAddress();
		$profiel->postcode = $faker->postcode();
		$profiel->woonplaats = $faker->city();
		$profiel->land = $faker->country();
		$profiel->telefoon = $faker->e164PhoneNumber(); // We hebben strenge telefoonnummer eisen
		$profiel->o_adres = $faker->streetAddress();
		$profiel->o_postcode = $faker->postcode();
		$profiel->o_woonplaats = $faker->city();
		$profiel->o_land = $faker->country();
		$profiel->o_telefoon = $faker->e164PhoneNumber();
		// contact
		$profiel->email = $faker->email();
		$profiel->sec_email = $faker->email();
		$profiel->mobiel = $faker->e164PhoneNumber();
		$profiel->linkedin = null;
		$profiel->website = null;
		// studie
		$profiel->studie = null;
		$profiel->studiejaar = $profiel->lidjaar;
		$profiel->beroep = null;
		// lidmaatschap
		$profiel->lidafdatum = null;
		$profiel->status = LidStatus::Lid;
		// geld
		$profiel->bankrekening = $faker->iban('NL');
		$profiel->machtiging = true;
		// verticale
		$profiel->moot = null;
		$profiel->verticale = static::getVerticale($faker);
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

		return $profiel;
	}

	/**
	 * @return mixed
	 */
	private static function getVerticale($faker)
	{
		return $faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I']);
	}
}
