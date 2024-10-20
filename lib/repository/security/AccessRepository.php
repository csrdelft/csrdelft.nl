<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\security\AccessControl;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method AccessControl|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccessControl|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccessControl[]    findAll()
 * @method AccessControl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccessRepository extends AbstractRepository
{
	/**
	 * @param ManagerRegistry $registry
	 * @param CacheInterface $cache
	 * @param EntityManagerInterface $em
	 */
	public function __construct(
		ManagerRegistry $registry,
		private readonly CacheInterface $cache,
		private readonly EntityManagerInterface $em
	) {
		parent::__construct($registry, AccessControl::class);
	}

	/**
	 * @param string $environment
	 * @param string $resource
	 *
	 * @return array
	 */
	public function getTree($environment, $resource)
	{
		$resources = [$resource, '*'];

		if ($environment === Activiteit::class) {
			$activiteit = $this->em->getRepository(Activiteit::class)->get($resource);
			if ($activiteit) {
				$resources[] = $activiteit->soort;
			}
		} elseif ($environment === Commissie::class) {
			$commissie = $this->em->getRepository(Commissie::class)->get($resource);
			if ($commissie) {
				$resources[] = $commissie->soort;
			}
		}

		return $this->createQueryBuilder('access')
			->where(
				'access.environment = :environment and access.resource in (:resources)'
			)
			->setParameter('environment', $environment)
			->setParameter('resources', $resources)
			->getQuery()
			->getResult();
	}

	/**
	 * Stel rechten in voor een specifiek of gehele klasse van objecten.
	 * Overschrijft bestaande rechten.
	 *
	 * @param string $environment
	 * @param string $resource
	 * @param array $acl
	 * @return bool
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function setAcl($environment, $resource, array $acl)
	{
		// Has permission to change permissions?
		if (!LoginService::mag(P_ADMIN)) {
			$rechten = $this->getSubject(
				$environment,
				AccessAction::Rechten,
				$resource
			);
			if (!$rechten || !LoginService::mag($rechten)) {
				return false;
			}
		}
		// Delete entire ACL for environment
		if (empty($resource)) {
			$this->createQueryBuilder('access')
				->delete()
				->where('access.environment = :environment')
				->setParameter('environment', $environment)
				->getQuery()
				->execute();
			return true;
		}
		// Delete entire ACL for object
		if ($acl === []) {
			$this->createQueryBuilder('access')
				->delete()
				->where(
					'access.environment = :environment and access.resource = :resource'
				)
				->setParameter('environment', $environment)
				->setParameter('resource', $resource)
				->getQuery()
				->execute();
			return true;
		}
		// CRUD ACL
		foreach ($acl as $action => $subject) {
			// Retrieve AC
			/** @var AccessControl $ac */
			$ac = $this->find([
				'environment' => $environment,
				'action' => $action,
				'resource' => $resource,
			]);
			// Delete AC
			if (empty($subject)) {
				if ($ac) {
					$this->_em->remove($ac);
				}
			}
			// Update AC
			elseif ($ac) {
				$ac->subject = $subject;
			}
			// Create AC
			else {
				$ac = $this->nieuw($environment, $resource);
				$ac->action = $action;
				$ac->subject = $subject;
				$this->_em->persist($ac);
			}
		}
		$this->_em->flush();
		return true;
	}

	/**
	 * @param string $environment
	 * @param string $action
	 * @param string $resource
	 *
	 * @return null|string
	 */
	public function getSubject($environment, $action, $resource)
	{
		$ac = $this->find([
			'environment' => $environment,
			'action' => $action,
			'resource' => $resource,
		]);
		if ($ac) {
			return $ac->subject;
		}
		return null;
	}

	/**
	 * @param string $environment
	 * @param string $resource
	 *
	 * @return AccessControl
	 */
	public function nieuw($environment, $resource)
	{
		$ac = new AccessControl();
		$ac->environment = $environment;
		$ac->resource = $resource;
		$ac->action = '';
		$ac->subject = '';
		return $ac;
	}
}
