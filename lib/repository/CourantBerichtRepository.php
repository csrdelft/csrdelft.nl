<?php


namespace CsrDelft\repository;


use CsrDelft\entity\courant\CourantBericht;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package CsrDelft\repository
 * @method CourantBericht|null find($id, $lockMode = null, $lockVersion = null)
 * @method CourantBericht|null findOneBy(array $criteria, array $orderBy = null)
 * @method CourantBericht[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourantBerichtRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CourantBericht::class);
	}

	public function getBerichtenVoorGebruiker()
	{
		//mods en bestuur zien alle berichten
		if (LoginService::mag(P_MAIL_COMPOSE) || LoginService::mag('bestuur')) {
			return $this->findAll();
		} else {
			return $this->findBy(['uid' => LoginService::getUid()], ['volgorde' => 'ASC']);
		}
	}

	/**
	 * @return CourantBericht[]
	 */
	public function findAll()
	{
		return $this->findBy([], ['volgorde' => 'ASC']);
	}
}
