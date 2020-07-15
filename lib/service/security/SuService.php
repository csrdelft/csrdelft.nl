<?php


namespace CsrDelft\service\security;


use CsrDelft\common\CsrException;
use CsrDelft\common\Security\TemporaryToken;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use Psr\Container\ContainerInterface;
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
	/**
	 * @var ContainerInterface
	 */
	private $container;

	public function __construct(Security $security, ContainerInterface $container, LoginService $loginService, AccountRepository $accountRepository) {
		$this->accountRepository = $accountRepository;
		$this->loginService = $loginService;
		$this->security = $security;
		$this->container = $container;
	}

	/**
	 * @return bool
	 */
	public function isSued() {
		return $this->security->getToken() && $this->security->isGranted('IS_IMPERSONATOR');
	}

	public function alsLid(Account $account, callable $fun) {
		$this->overrideUid($account);

		$result = null;

		try {
			$result = $fun();
		} finally {
			$this->resetUid();
		}

		return $result;
	}

	/**
	 * Schakel tijdelijk naar een lid om gedrag van functies te simuleren alsof dit lid is ingelogd.
	 * Moet z.s.m. (binnen dit request) weer ongedaan worden met `endTempSwitchUser()`
	 * @param Account $account Account van lid waarnaartoe geschakeld moet worden
	 * @throws CsrException als er al een tijdelijke schakeling actief is.
	 */
	public function overrideUid(Account $account) {
		$token = $this->security->getToken();
		if ($token instanceof TemporaryToken) {
			throw new CsrException("Er is al een tijdelijke schakeling actief, beëindig deze eerst.");
		}

		$temporaryToken = new TemporaryToken($account, $token);

		$this->container->get('security.token_storage')->setToken($temporaryToken);
	}

	/**
	 * Beëindig tijdelijke schakeling naar lid.
	 * @throws CsrException als er geen tijdelijke schakeling actief is.
	 */
	public function resetUid() {
		$token = $this->security->getToken();
		if (!($token instanceof TemporaryToken)) {
			throw new CsrException("Geen tijdelijke schakeling actief, kan niet terug.");
		}

		$this->container->get('security.token_storage')->setToken($token->getOriginalToken());
	}
}
