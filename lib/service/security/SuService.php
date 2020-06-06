<?php


namespace CsrDelft\service\security;


use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;

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

	public function __construct(LoginService $loginService, AccountRepository $accountRepository) {
		$this->accountRepository = $accountRepository;
		$this->loginService = $loginService;
	}

	/**
	 * @param string $uid
	 *
	 * @throws CsrGebruikerException
	 */
	public function switchUser($uid) {
		if ($this->isSued()) {
			throw new CsrGebruikerException('Geneste su niet mogelijk!');
		}
		$suNaar = $this->accountRepository->get($uid);
		if (!$this->maySuTo($suNaar)) {
			throw new CsrGebruikerException('Deze gebruiker mag niet inloggen!');
		}
		$suedFrom = $this->loginService->getAccount();
		// Keep authentication method
		$authMethod = $this->loginService->getAuthenticationMethod();

		// Clear session
		session_unset();

		// Subject assignment:
		$_SESSION[LoginService::SESS_SUED_FROM] = $suedFrom->uid;
		$_SESSION[LoginService::SESS_UID] = $suNaar->uid;
		$_SESSION[LoginService::SESS_AUTHENTICATION_METHOD] = $authMethod;
	}

	/**
	 */
	public function endSwitchUser() {
		$suedFrom = static::getSuedFrom();
		// Keep authentication method
		$authMethod = $this->loginService->getAuthenticationMethod();

		// Clear session
		session_unset();

		// Subject assignment:
		$_SESSION[LoginService::SESS_UID] = $suedFrom->uid;
		$_SESSION[LoginService::SESS_SUED_FROM] = null;
		$_SESSION[LoginService::SESS_AUTHENTICATION_METHOD] = $authMethod;
	}
	/**
	 * @return bool
	 */
	public function isSued() {
		if (!isset($_SESSION[LoginService::SESS_SUED_FROM])) {
			return false;
		}
		$suedFrom = static::getSuedFrom();
		return $suedFrom && AccessService::mag($suedFrom, P_ADMIN);
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



	/**
	 * @param Account $suNaar
	 *
	 * @return bool
	 */
	public function maySuTo(Account $suNaar) {
		return LoginService::mag(P_ADMIN) && !$this->isSued() && $suNaar->uid !== LoginService::getUid() && AccessService::mag($suNaar, P_LOGGED_IN);
	}

	/**
	 * @return Account|null
	 */
	public static function getSuedFrom() {
		return AccountRepository::get($_SESSION[LoginService::SESS_SUED_FROM]);
	}

}
