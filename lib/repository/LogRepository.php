<?php

namespace CsrDelft\repository;

use CsrDelft\entity\LogEntry;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class LogRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, LogEntry::class);
	}

	public function opschonen() {
		$this->getEntityManager()
			->createQuery('DELETE CsrDelft\entity\LogEntry l WHERE l.moment < :moment')
			->setParameter('moment', date_create_immutable('-2 months'))
			->execute();
	}

	public function log() {
		$entry = new LogEntry();
		if (isset($_SESSION[LoginService::SESS_SUED_FROM])) {
			$entry->uid = $_SESSION[LoginService::SESS_SUED_FROM];
		} elseif (isset($_SESSION[LoginService::SESS_UID])) {
			$entry->uid = $_SESSION[LoginService::SESS_UID];
		} else {
			$entry->uid = 'fout';
		}
		$entry->moment = date_create_immutable();
		$entry->locatie = '';
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$entry->ip = $_SERVER['REMOTE_ADDR'];
		} else {
			// Niet een extern request
			return;
		}
		if (isset($_SERVER['REQUEST_URI'])) {
			$entry->url = $_SERVER['REQUEST_URI'];
		} else {
			$entry->url = '';
		}
		if (isset($_SERVER['HTTP_REFERER'])) {
			$entry->referer = $_SERVER['HTTP_REFERER'];
		} else {
			$entry->referer = '';
		}
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$entry->useragent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$entry->useragent = '';
		}
		$entry->removeOverflow();

		$this->getEntityManager()->persist($entry);
		$this->getEntityManager()->flush();
	}

}
