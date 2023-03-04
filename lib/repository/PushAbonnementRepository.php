<?php

namespace CsrDelft\repository;

use CsrDelft\entity\PushAbonnement;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author S. Benjamins <sebastiaan@benjami.in>
 *
 * @method PushAbonnement|null find($id, $lockMode = null, $lockVersion = null)
 * @method PushAbonnement|null findOneBy(array $criteria, array $orderBy = null)
 * @method PushAbonnement[]    findAll()
 * @method PushAbonnement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PushAbonnementRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, PushAbonnement::class);
	}

	/**
	 * @param null $soort
	 * @return PushAbonnement
	 */
	public function nieuw()
	{
		$item = new PushAbonnement();
		$item->uid = LoginService::getUid();
		$item->client_endpoint = null;
		$item->client_keys = null;
		return $item;
	}
}
