<?php

namespace CsrDelft\repository;

use CsrDelft\entity\WebPush;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author S. Benjamins <sebastiaan@benjami.in>
 *
 * @method WebPush|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebPush|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebPush[]    findAll()
 * @method WebPush[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebPushRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, WebPush::class);
	}

	/**
	 * @param null $soort
	 * @return WebPush
	 */
	public function nieuw()
	{
		$item = new WebPush();
		$item->uid = LoginService::getUid();
		$item->clientEndpoint = null;
		$item->clientKeys = null;
		return $item;
	}
}
