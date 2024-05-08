<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\security\RememberLogin;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method RememberLogin|null find($id, $lockMode = null, $lockVersion = null)
 * @method RememberLogin|null findOneBy(array $criteria, array $orderBy = null)
 * @method RememberLogin[]    findAll()
 * @method RememberLogin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method RememberLogin|null retrieveByUuid($UUID)
 */
class RememberLoginRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, RememberLogin::class);
	}

	/**
	 * @return RememberLogin
	 */
	public function nieuw(): RememberLogin
	{
		$remember = new RememberLogin();
		$remember->uid = LoginService::getUid();
		$remember->profiel = LoginService::getProfiel();
		$remember->remember_since = date_create_immutable();
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$remember->device_name = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$remember->device_name = '';
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$remember->ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$remember->ip = '';
		}
		$remember->lock_ip = false;
		return $remember;
	}

	/**
	 * @param $token
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijder($token)
	{
		$rememberLogin = $this->findOneBy(['token' => $token]);
		if ($rememberLogin) {
			$this->getEntityManager()->remove($rememberLogin);
			$this->getEntityManager()->flush();
		}
	}
}
