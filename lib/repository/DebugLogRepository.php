<?php

namespace CsrDelft\repository;

use Symfony\Component\HttpFoundation\Request;
use CsrDelft\common\FlashType;
use CsrDelft\entity\DebugLogEntry;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class DebugLogRepository extends AbstractRepository
{
	public function __construct(
		ManagerRegistry $registry,
		private readonly RequestStack $requestStack,
		private readonly Security $security
	) {
		parent::__construct($registry, DebugLogEntry::class);
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
		if ($this->requestStack->getCurrentRequest() instanceof Request) {
			$entry->request =
				$this->requestStack->getCurrentRequest()->getRequestUri() ?: 'CLI';
		} else {
			$entry->request = 'CLI';
		}
		$entry->user_agent = @$_SERVER['HTTP_USER_AGENT'] ?: 'CLI';

		$this->getEntityManager()->persist($entry);
		if (
			DEBUG &&
			$this->getEntityManager()
				->getConnection()
				->isTransactionActive()
		) {
			$flashBag = $this->requestStack->getSession()->getFlashBag();
			$flashBag->add(
				FlashType::WARNING,
				'Debug log may not be committed: database transaction'
			);
			$flashBag->add(FlashType::INFO, $dump);
		}
		$this->getEntityManager()->flush();
		return $entry;
	}
}
