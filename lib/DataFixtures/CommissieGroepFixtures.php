<?php

namespace CsrDelft\DataFixtures;

use CsrDelft\DataFixtures\Util\FixtureHelpers;
use CsrDelft\DataFixtures\Util\GroepFixtureUtil;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\groepen\CommissiesRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommissieGroepFixtures extends Fixture implements
	DependentFixtureInterface
{
	public function load(ObjectManager $manager)
	{
		/** @var CommissiesRepository $commissiesRepository */
		$commissiesRepository = $manager->getRepository(Commissie::class);
		$soccie = $commissiesRepository->nieuw();
		$soccie->familie = 'SocCie';
		$soccie->naam = 'SocCie Test';

		$manager->persist($soccie);
		$manager->flush();

		GroepFixtureUtil::maakGroepLid(
			$manager,
			$soccie,
			$this->getReference(AccountFixtures::UID_SOCCIE_PRAESES),
			'Praeses'
		);
		GroepFixtureUtil::maakGroepLid(
			$manager,
			$soccie,
			$this->getReference(AccountFixtures::UID_SOCCIE_FISCUS),
			'Fiscus'
		);

		// Stop een aantal random leden in deze commissie
		foreach (FixtureHelpers::getRandomUids(8) as $uid) {
			GroepFixtureUtil::maakGroepLid(
				$manager,
				$soccie,
				$manager->getRepository(Profiel::class)->find($uid)
			);
		}

		$manager->flush();
	}

	public function getDependencies()
	{
		return [AccountFixtures::class, ProfielFixtures::class];
	}
}
