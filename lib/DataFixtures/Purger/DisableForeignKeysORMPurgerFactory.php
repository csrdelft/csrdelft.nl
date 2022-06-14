<?php

namespace CsrDelft\DataFixtures\Purger;

use Doctrine\Bundle\FixturesBundle\Purger\PurgerFactory;
use Doctrine\Common\DataFixtures\Purger\PurgerInterface;
use Doctrine\ORM\EntityManagerInterface;

class DisableForeignKeysORMPurgerFactory implements PurgerFactory
{
	public function createForEntityManager(
		?string $emName,
		EntityManagerInterface $em,
		array $excluded = [],
		bool $purgeWithTruncate = false
	): PurgerInterface {
		return new DisableForeignKeysORMPurger($em);
	}
}
