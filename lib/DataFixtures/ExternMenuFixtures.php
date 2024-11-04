<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\MenuItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ExternMenuFixtures extends Fixture
{
	/**
	 * @return void
	 */
	public function load(ObjectManager $manager)
	{
		$externMenuItem = $this->nieuwMenuItem(
			null,
			0,
			'extern',
			'/',
			true,
			P_PUBLIC
		);
		$manager->persist($externMenuItem);

		$verenigingMenuItem = $this->nieuwMenuItem(
			$externMenuItem,
			10,
			'Vereniging',
			'/vereniging',
			true,
			P_PUBLIC
		);

		$manager->persist($verenigingMenuItem);

		$manager->flush();
	}

	private function nieuwMenuItem(
		MenuItem|null $parent,
		int $volgorde,
		string $tekst,
		string $link,
		bool $zichtbaar,
		string $rechten_bekijken
	): MenuItem {
		$menuItem = new MenuItem();
		$menuItem->parent = $parent;
		$menuItem->volgorde = $volgorde;
		$menuItem->tekst = $tekst;
		$menuItem->link = $link;
		$menuItem->zichtbaar = $zichtbaar;
		$menuItem->rechten_bekijken = $rechten_bekijken;

		return $menuItem;
	}
}
