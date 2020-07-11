<?php


namespace CsrDelft\service\security;


use CsrDelft\common\CsrException;
use CsrDelft\repository\security\AccountRepository;
use Symfony\Component\Security\Core\Security;

class SuService {
	private $tempSwitchUid;

	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var LoginService
	 */
	private $loginService;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security, LoginService $loginService, AccountRepository $accountRepository) {
		$this->accountRepository = $accountRepository;
		$this->loginService = $loginService;
		$this->security = $security;
	}

	/**
	 * @return bool
	 */
	public function isSued() {
		return $this->security->isGranted('IS_IMPERSONATOR');
	}

	/**
	 * Schakel tijdelijk naar een lid om gedrag van functies te simuleren alsof dit lid is ingelogd.
	 * Moet z.s.m. (binnen dit request) weer ongedaan worden met `endTempSwitchUser()`
	 * @param string $uid Uid van lid waarnaartoe geschakeld moet worden
	 * @throws CsrException als er al een tijdelijke schakeling actief is.
	 */
	public function overrideUid($uid) {
		if (isset($this->tempSwitchUid)) {
			throw new CsrException("Er is al een tijdelijke schakeling actief, beëindig deze eerst.");
		}
		$this->tempSwitchUid = $_SESSION[LoginService::SESS_UID];
		$_SESSION[LoginService::SESS_UID] = $uid;
	}

	/**
	 * Beëindig tijdelijke schakeling naar lid.
	 * @throws CsrException als er geen tijdelijke schakeling actief is.
	 */
	public function resetUid() {
		if (!isset($this->tempSwitchUid)) {
			throw new CsrException("Geen tijdelijke schakeling actief, kan niet terug.");
		}
		$_SESSION[LoginService::SESS_UID] = $this->tempSwitchUid;
		$this->tempSwitchUid = null;
	}
}
