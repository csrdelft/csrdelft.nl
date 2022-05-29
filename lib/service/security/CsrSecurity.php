<?php

namespace CsrDelft\service\security;

use CsrDelft\common\CsrException;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use Symfony\Component\Security\Core\Security;

/**
 * Vraag de huidige gebruiker op.
 */
class CsrSecurity
{
	/**
	 * @var Security
	 */
	private $security;
	/**
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var AccessService
	 */
	private $accessService;

	public function __construct(Security $security, AccessService $accessService, AccountRepository $accountRepository)
	{
		$this->security = $security;
		$this->accountRepository = $accountRepository;
		$this->accessService = $accessService;
	}

	private function getExternAccount(): Account
	{
		$externAccount = $this->accountRepository->find(LoginService::UID_EXTERN);

		if (!$externAccount) {
			throw new CsrException("Extern account bestaat niet!");
		}

		return $externAccount;
	}

	public function getAccount(): Account
	{
		if (isCLI()) {
			return $this->getExternAccount();
		}
		$user = $this->security->getUser();
		if ($user instanceof Account) {
			return $user;
		} else {
			return $this->getExternAccount();
		}
	}

	public function getProfiel(): Profiel
	{
		return $this->getAccount()->profiel;
	}

	public function mag($permission, array $allowedAuthenticationMethdos = null): bool
	{
		return $this->accessService->mag($this->getAccount(), $permission, $allowedAuthenticationMethdos);
	}
}
