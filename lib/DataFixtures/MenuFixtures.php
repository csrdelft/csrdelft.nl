<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\entity\MenuItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends Fixture
{
	public function load(ObjectManager $manager)
	{
		$mainMenuItem = $this->nieuwMenuItem(null, 0, 'main', '/', true, P_PUBLIC);
		$manager->persist($mainMenuItem);

		$personalMenuItem = $this->nieuwMenuItem(null, 5, 'Personal', '/profiel', true, P_LOGGED_IN);
		$manager->persist($personalMenuItem);

		$remoteForaMenuItem = $this->nieuwMenuItem(null, 0, 'remotefora', '/', true, P_PUBLIC);
		$manager->persist($remoteForaMenuItem);

		$manager->flush();
	}

	private function nieuwMenuItem($parent, $volgorde, $tekst, $link, $zichtbaar, $rechten_bekijken)
	{
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
