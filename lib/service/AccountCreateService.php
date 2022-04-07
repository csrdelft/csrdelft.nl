<?php

namespace CsrDelft\service;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class AccountCreateService
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

	public function __construct(CiviSaldoRepository    $civiSaldoRepository,
															MenuItemRepository     $menuItemRepository,
															AccessService          $accessService,
															EntityManagerInterface $manager)
	{
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->menuItemRepository = $menuItemRepository;
		$this->accessService = $accessService;
		$this->manager = $manager;
	}


	/**
	 * @param string $uid
	 *
	 * @return Account
	 * @throws CsrGebruikerException
	 */
	public function maakAccount($uid)
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
		$account->perm_role = $this->accessService->getDefaultPermissionRole($profiel->status);
		$this->manager->persist($account);
		$this->manager->flush();
		return $account;
	}
}
