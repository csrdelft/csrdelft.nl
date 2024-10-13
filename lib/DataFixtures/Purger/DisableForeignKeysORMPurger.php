<?php

namespace CsrDelft\DataFixtures\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class DisableForeignKeysORMPurger extends ORMPurger
{
	public function purge(): void
	{
		$connection = $this->getObjectManager()->getConnection();

		try {
			$connection->executeQuery('SET FOREIGN_KEY_CHECKS=0;');
			parent::purge();
		} finally {
			$connection->executeQuery('SET FOREIGN_KEY_CHECKS=1;');
		}
	}
}
