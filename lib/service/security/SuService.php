<?php

namespace CsrDelft\service\security;

use CsrDelft\common\CsrException;
use CsrDelft\common\Security\TemporaryToken;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class SuService
{
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
	 * @var TokenStorageInterface
	 */
	private $tokenStorage;
	/**
	 * @var AccessService
	 */
	private $accessService;

	public function __construct(
		Security $security,
		LoginService $loginService,
		AccountRepository $accountRepository,
		TokenStorageInterface $tokenStorage,
		AccessService $accessService
	) {
		$this->accountRepository = $accountRepository;
		$this->loginService = $loginService;
		$this->security = $security;
		$this->tokenStorage = $tokenStorage;
		$this->accessService = $accessService;
	}

	/**
	 * @return bool
	 */
	public function isSued()
	{
		return $this->security->getToken() &&
			$this->security->isGranted('IS_IMPERSONATOR');
	}

	/**
	 * Voer een callable uit alsof je bent ingelogd als $account.
	 *
	 * @param Account $account
	 * @param callable $fun
	 * @return mixed Het resultaat van $fun
	 */
	public function alsLid(Account $account, callable $fun)
	{
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
	 * @see SuService::alsLid() voor een veilige methode
	 */
	public function overrideUid(Account $account)
	{
		$token = $this->security->getToken();
		if ($token instanceof TemporaryToken) {
			throw new CsrException(
				'Er is al een tijdelijke schakeling actief, beëindig deze eerst.'
			);
		}

		$temporaryToken = new TemporaryToken($account, $token);

		$this->tokenStorage->setToken($temporaryToken);
	}

	/**
	 * Beëindig tijdelijke schakeling naar lid.
	 * @throws CsrException als er geen tijdelijke schakeling actief is.
	 * @see SuService::alsLid() voor een veilige methode
	 */
	public function resetUid()
	{
		$token = $this->security->getToken();
		if (!($token instanceof TemporaryToken)) {
			throw new CsrException(
				'Geen tijdelijke schakeling actief, kan niet terug.'
			);
		}

		$this->tokenStorage->setToken($token->getOriginalToken());
	}

	public function maySuTo(UserInterface $suNaar)
	{
		return $this->security->isGranted('ROLE_ALLOWED_TO_SWITCH') && // Mag switchen
		!$this->security->isGranted('IS_IMPERSONATOR') && // Is niet al geswitched
		$this->security->getUser()->getUsername() !== $suNaar->getUsername() && // Is niet dezelfde gebruiker
			$this->accessService->isUserGranted($suNaar, 'ROLE_LOGGED_IN'); // Gebruiker waar naar geswitched wordt mag inloggen
	}
}
