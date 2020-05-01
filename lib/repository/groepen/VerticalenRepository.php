<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Verticale;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class VerticalenRepository extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Verticale::class);
	}

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'letter ASC';

	public function get($letter) {
		if ($verticale = $this->findOneBy(['letter' => $letter])) {
			return $verticale;
		}

		return parent::get($letter);
	}

	/**
	 * @param $naam
	 * @return Verticale|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function searchByNaam($naam) {
		return $this->createQueryBuilder('v')
			->where('v.naam LIKE :naam')
			->setParameter('naam', sql_contains($naam))
			->setMaxResults(1)
			->getQuery()->getOneOrNullResult();
	}

	public function nieuw($soort = null) {
		/** @var Verticale $verticale */
		$verticale = parent::nieuw();
		$verticale->letter = null;
		return $verticale;
	}

}
