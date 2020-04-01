<?php

namespace CsrDelft\repository;

use CsrDelft\entity\DebugLogEntry;
use CsrDelft\model\security\LoginModel;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class DebugLogRepository extends AbstractRepository {
	/**
	 * @var LoginModel
	 */
	private $loginModel;

	public function __construct(ManagerRegistry $registry, LoginModel $loginModel) {
		parent::__construct($registry, DebugLogEntry::class);

		$this->loginModel = $loginModel;
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
		$entry->uid = LoginModel::getUid();
		if ($this->loginModel->isSued()) {
			$entry->su_uid = LoginModel::getSuedFrom()->uid;
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
