<?php

namespace CsrDelft\repository;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GroepLid|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroepLid|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroepLid[]    findAll()
 * @method GroepLid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroepLidRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $managerRegistry)
	{
		parent::__construct($managerRegistry, GroepLid::class);
	}

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'lid_sinds ASC';

	/**
	 * @param Groep $groep
	 * @param $uid
	 *
	 * @return GroepLid|null
	 */
	public function get(Groep $groep, $uid)
	{
		return $this->find(['groep_id' => $groep->id, 'uid' => $uid]);
	}

	/**
	 * @param Groep $groep
	 * @param $uid
	 *
	 * @return GroepLid
	 */
	public function nieuw(Groep $groep, $uid)
	{
		$lid = new GroepLid();
		$lid->groep = $groep;
		$lid->groepId = $groep->id;
		$lid->uid = $uid;
		$lid->profiel = $uid ? ProfielRepository::get($uid) : null;
		$lid->doorUid = LoginService::getUid();
		$lid->doorProfiel = LoginService::getProfiel();
		$lid->lidSinds = date_create_immutable();
		$lid->opmerking = null;
		return $lid;
	}
}
