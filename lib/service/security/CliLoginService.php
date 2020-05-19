<?php

namespace CsrDelft\service\security;

use CsrDelft\entity\security\LoginSession;
use CsrDelft\entity\security\RememberLogin;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\repository\security\LoginSessionRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use DateInterval;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Model van het huidige ingeloggede account in CLI modus.
 */
class CliLoginService implements ILoginService {
	/**
	 * @var string
	 */
	protected static $uid = LoginService::UID_EXTERN;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;
	/**
	 * @var LoginSessionRepository
	 */
	private $loginRepository;

	/**
	 * @return string
	 */
	public static function getUid() {
		return self::$uid;
	}

	/**
	 * CliLoginModel constructor.
	 * @param AccountRepository $accountRepository
	 * @param LoginSessionRepository $loginRepository
	 * @param RememberLoginRepository $rememberLoginRepository
	 */
	public function __construct(AccountRepository $accountRepository, LoginSessionRepository $loginRepository, RememberLoginRepository $rememberLoginRepository) {
		$this->accountRepository = $accountRepository;
		$this->rememberLoginRepository = $rememberLoginRepository;
		$this->loginRepository = $loginRepository;
	}

	public function authenticate() {
		if (!$this->validate()) {
			die('access denied');
		}
	}

	/**
	 * @return bool
	 */
	public function validate() {
		return $this->login(env('CRON_USER'), env('CRON_PASS'));
	}

	/**
	 * @param string $user
	 * @param string $pass_plain
	 * @param bool $evtWachten
	 * @param RememberLogin|null $remember
	 * @param bool $lockIP
	 * @param bool $alreadyAuthenticatedByUrlToken
	 * @param string $expire
	 *
	 * @return bool
	 */
	public function login($user, $pass_plain, $evtWachten = true, RememberLogin $remember = null, $lockIP = false, $alreadyAuthenticatedByUrlToken = false, $expire = null) {
		$user = filter_var($user, FILTER_SANITIZE_STRING);
		$pass_plain = filter_var($pass_plain, FILTER_SANITIZE_STRING);

		// Inloggen met lidnummer of gebruikersnaam
		if ($this->accountRepository->isValidUid($user)) {
			$account = $this->accountRepository->get($user);
		} else {
			$account = $this->accountRepository->findOneByUsername($user);
		}

		// Onbekende gebruiker
		if (!$account) {
			die('Inloggen niet geslaagd');
		}

		// Clear session
		session_unset();

		// Check password
		if ($this->accountRepository->controleerWachtwoord($account, $pass_plain)) {
			$this->accountRepository->successfulLoginAttempt($account);
		} // Wrong password
		else {
			// Password deleted (by admin)
			if ($account->pass_hash == '') {
				die('Gebruik wachtwoord vergeten of mail de PubCie');
			} // Regular failed username+password
			else {
				$this->accountRepository->failedLoginAttempt($account);
				die('Inloggen niet geslaagd');
			}
		}

		// Subject assignment:
		self::$uid = $account->uid;

		// Login sessie aanmaken in database
		$session = new LoginSession();
		$session->session_hash = hash('sha512', session_id());
		$session->uid = $account->uid;
		$session->login_moment = date_create_immutable();
		$session->expire = date_create_immutable()->add(new DateInterval('PT' . getSessionMaxLifeTime() . 'S'));
		$session->user_agent = MODE;
		$session->ip = '';
		$session->lock_ip = true; // sessie koppelen aan ip?
		$session->authentication_method = $this->getAuthenticationMethod();
		$this->loginRepository->update($session);

		return true;
	}

	/**
	 */
	public function logout() {
		self::$uid = LoginService::UID_EXTERN;
	}

	/**
	 * @return string
	 */
	public function getAuthenticationMethod() {
		return AuthenticationMethod::password_login;
	}
}
