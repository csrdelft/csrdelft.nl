<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class AccountService
{
	/**
	 * @var CiviSaldoRepository
	 */
	private $civiSaldoRepository;
	/**
	 * @var MenuItemRepository
	 */
	private $menuItemRepository;
	/**
	 * @var AccessService
	 */
	private $accessService;
	/**
	 * @var EntityManagerInterface
	 */
	private $manager;
	/**
	 * @var PasswordHasherFactoryInterface
	 */
	private $passwordHasherFactory;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;

	public function __construct(
		CiviSaldoRepository $civiSaldoRepository,
		MenuItemRepository $menuItemRepository,
		AccessService $accessService,
		ProfielRepository $profielRepository,
		PasswordHasherFactoryInterface $passwordHasherFactory,
		EntityManagerInterface $manager
	) {
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->menuItemRepository = $menuItemRepository;
		$this->accessService = $accessService;
		$this->manager = $manager;
		$this->passwordHasherFactory = $passwordHasherFactory;
		$this->profielRepository = $profielRepository;
	}

	/**
	 * @param string $uid
	 *
	 * @return Account
	 * @throws CsrGebruikerException
	 */
	public function maakAccount($uid): Account
	{
		$profiel = ProfielRepository::get($uid);
		if (!$profiel) {
			throw new CsrGebruikerException('Profiel bestaat niet');
		}

		if (!$this->civiSaldoRepository->getSaldo($uid)) {
			// Maak een CiviSaldo voor dit account
			$this->civiSaldoRepository->maakSaldo($uid);
		}

		if (!$this->menuItemRepository->getMenuRoot($uid)) {
			$menuItem = $this->menuItemRepository->nieuw(null);
			$menuItem->rechten_bekijken = $uid;
			$menuItem->tekst = $uid;
			$menuItem->link = '';

			$this->manager->persist($menuItem);
		}

		$account = new Account();
		$account->uuid = Uuid::v4();
		$account->uid = $uid;
		$account->profiel = $profiel;
		$account->username = $uid;
		$account->email = $profiel->email;
		$account->pass_hash = '';
		$account->pass_since = null;
		$account->failed_login_attempts = 0;
		$account->perm_role = $this->accessService->getDefaultPermissionRole(
			$profiel->status
		);
		$this->manager->persist($account);
		$this->manager->flush();
		return $account;
	}

	/**
	 * Reset het wachtwoord van de gebruiker.
	 *  - Controleert GEEN eisen aan wachtwoord
	 *  - Wordt NIET gelogged in de changelog van het profiel
	 * @param Account $account
	 * @param $passPlain
	 * @param bool $isVeranderd
	 * @return bool
	 */
	public function wijzigWachtwoord(
		Account $account,
		$passPlain,
		bool $isVeranderd = true
	): bool {
		if ($passPlain != '') {
			$account->pass_hash = $this->maakWachtwoord($account, $passPlain);
			if ($isVeranderd) {
				$account->pass_since = date_create_immutable();
			}
		}
		$this->manager->persist($account);
		$this->manager->flush();

		if ($isVeranderd) {
			// Sync LDAP
			$profiel = $account->profiel;
			if ($profiel) {
				$profiel->email = $account->email;
				$this->profielRepository->update($profiel);
			}
		}

		return true;
	}

	/**
	 * Create SSH hash.
	 *
	 * @param Account $account
	 * @param string $passPlain
	 * @return string
	 */
	public function maakWachtwoord(Account $account, $passPlain): string
	{
		return $this->passwordHasherFactory
			->getPasswordHasher($account)
			->hash($passPlain, $account->getSalt());
	}

	/**
	 * Verify SSHA hash.
	 *
	 * @param UserInterface $account
	 * @param string $passPlain
	 * @return boolean
	 */
	public function controleerWachtwoord(UserInterface $account, $passPlain): bool
	{
		// Controleer of het wachtwoord klopt
		return $this->passwordHasherFactory
			->getPasswordHasher($account)
			->verify($account->getPassword(), $passPlain, $account->getSalt());
	}
}
