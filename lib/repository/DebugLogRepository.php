<?php

namespace CsrDelft\repository;

use CsrDelft\entity\DebugLogEntry;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class DebugLogRepository extends AbstractRepository
{
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var RequestStack
	 */
	private $requestStack;

	public function __construct(
		ManagerRegistry $registry,
		RequestStack $requestStack,
		Security $security
	) {
		parent::__construct($registry, DebugLogEntry::class);

		$this->security = $security;
		$this->requestStack = $requestStack;
	}

	/**
	 */
	public function opschonen()
	{
		$this->createQueryBuilder('l')
			->delete()
			->where('l.moment < :moment')
			->setParameter('moment', date_create_immutable('-2 months'))
			->getQuery()
			->execute();
	}

	/**
	 * @param string $class
	 * @param string $function
	 * @param string[] array $args
	 * @param string $dump
	 *
	 * @return DebugLogEntry
	 */
	public function log($class, $function, array $args = [], $dump = null)
	{
		$entry = new DebugLogEntry();
		$entry->class_function =
			$class . '->' . $function . '(' . implode(', ', $args) . ')';
		$entry->dump = $dump;
		$exception = new Exception();
		$entry->call_trace = $exception->getTraceAsString();
		$entry->moment = date_create_immutable();
		$entry->uid = LoginService::getUid();
		$token = $this->security->getToken();
		if ($token instanceof SwitchUserToken) {
			$entry->su_uid = $token->getOriginalToken()->getUsername();
		}
		$entry->ip = @$_SERVER['REMOTE_ADDR'] ?: '127.0.0.1';
		$entry->referer = @$_SERVER['HTTP_REFERER'] ?: 'CLI';
		$entry->request =
			$this->requestStack->getCurrentRequest()->getRequestUri() ?: 'CLI';
		$entry->user_agent = @$_SERVER['HTTP_USER_AGENT'] ?: 'CLI';

		$this->getEntityManager()->persist($entry);
		if (
			DEBUG and
			$this->getEntityManager()
				->getConnection()
				->isTransactionActive()
		) {
			setMelding('Debug log may not be committed: database transaction', 2);
			setMelding($dump, 0);
		}
		$this->getEntityManager()->flush();
		return $entry;
	}
}
