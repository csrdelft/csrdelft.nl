<?php

namespace CsrDelft\repository;

use CsrDelft\entity\DebugLogEntry;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class DebugLogRepository extends AbstractRepository {
	/**
	 * @var SuService
	 */
	private $suService;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(ManagerRegistry $registry, Security $security, SuService $suService) {
		parent::__construct($registry, DebugLogEntry::class);

		$this->suService = $suService;
		$this->security = $security;
	}

	/**
	 */
	public function opschonen() {
		$this->createQueryBuilder('l')
			->delete()
			->where('l.moment < :moment')
			->setParameter('moment', date_create_immutable('-2 months'))
			->getQuery()->execute();
	}

	/**
	 * @param string $class
	 * @param string $function
	 * @param string[] array $args
	 * @param string $dump
	 *
	 * @return DebugLogEntry
	 */
	public function log($class, $function, array $args = array(), $dump = null) {
		$entry = new DebugLogEntry();
		$entry->class_function = $class . '->' . $function . '(' . implode(', ', $args) . ')';
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
		$entry->request = REQUEST_URI ?: 'CLI';
		$entry->user_agent = @$_SERVER['HTTP_USER_AGENT'] ?: 'CLI';

		$this->getEntityManager()->persist($entry);
		if (DEBUG AND $this->getEntityManager()->getConnection()->isTransactionActive()) {
			setMelding('Debug log may not be committed: database transaction', 2);
			setMelding($dump, 0);
		}
		$this->getEntityManager()->flush();
		return $entry;
	}

}
