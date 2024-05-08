<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\MenuItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ExternMenuFixtures extends Fixture
{
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
		$parent,
		$volgorde,
		$tekst,
		$link,
		$zichtbaar,
		$rechten_bekijken
	) {
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
