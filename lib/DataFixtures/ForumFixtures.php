<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\forum\ForumCategorie;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as Faker;

class ForumFixtures extends Fixture
{
	public function load(ObjectManager $manager)
	{
		$forumCategorie = new ForumCategorie();
		$forumCategorie->titel = 'Algemeen';
		$forumCategorie->volgorde = 0;
		$forumCategorie->rechten_lezen = P_LOGGED_IN;

		$manager->persist($forumCategorie);

		$forum = new ForumDeel();
		$forum->rechten_lezen = P_LOGGED_IN;
		$forum->titel = 'Algemeen Deel';
		$forum->omschrijving = 'Een algemeen deel';
		$forum->categorie = $forumCategorie;
		$forum->volgorde = 0;
		$forum->rechten_lezen = P_LOGGED_IN;
		$forum->rechten_modereren = P_ADMIN;
		$forum->rechten_posten = P_LOGGED_IN;

		$manager->persist($forum);

		$faker = Faker::create('nl_NL');

		for ($i = 0; $i < 100; $i++) {
			$draad = new ForumDraad();
			$draad->titel = $faker->sentence;
			$draad->uid = FixtureHelpers::getUid();
			$draad->deel = $forum;
			$draad->datum_tijd = $faker->dateTimeThisMonth;
			$draad->gesloten = false;
			$draad->laatst_gewijzigd = $draad->datum_tijd;
			$draad->laatste_wijziging_uid = $draad->uid;
			$draad->verwijderd = false;
			$draad->wacht_goedkeuring = false;
			$draad->plakkerig = false;
			$draad->eerste_post_plakkerig = false;
			$draad->pagina_per_post = false;

			$manager->persist($draad);

			$posts = $faker->numberBetween(1, 50);

			for ($j = 0; $j < $posts; $j++) {
				$post = new ForumPost();
				$post->datum_tijd = $faker->dateTimeBetween($draad->datum_tijd->format(DATE_RFC2822), 'now');
				$post->laatst_gewijzigd = $post->datum_tijd;
				$post->uid = FixtureHelpers::getUid();
				$post->draad = $draad;
				$post->verwijderd = false;
				$post->auteur_ip = "::1";
				$post->wacht_goedkeuring = false;
				$post->tekst = implode("", array_map(function ($p) { return '[p]'. $p . '[/p]';},$faker->paragraphs));

				$manager->persist($post);
			}
		}

		$manager->flush();
	}
}
