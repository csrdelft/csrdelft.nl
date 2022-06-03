<?php

namespace CsrDelft\repository\security;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\AccessService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

/**
 * AccountRepository
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Wachtwoord en login timeout management.
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends AbstractRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    /**
     * @var AccessService
     */
    private $accessService;
    /**
     * @var CiviSaldoRepository
     */
    private $civiSaldoRepository;
    /**
     * @var MenuItemRepository
     */
    private $menuItemRepository;
    /**
     * @var PasswordHasherFactoryInterface
     */
    private $passwordHasherFactory;

    public function __construct(
        ManagerRegistry                $registry,
        PasswordHasherFactoryInterface $passwordHasherFactory,
        AccessService                  $accessService,
        CiviSaldoRepository            $civiSaldoRepository,
        MenuItemRepository             $menuItemRepository
    )
    {
        parent::__construct($registry, Account::class);
        $this->accessService = $accessService;
        $this->civiSaldoRepository = $civiSaldoRepository;
        $this->menuItemRepository = $menuItemRepository;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * @param $uid
     * @return Account|null
     */
    public static function get($uid)
    {
        $accountRepository = ContainerFacade::getContainer()->get(AccountRepository::class);
        return $accountRepository->find($uid);
    }

    /**
     * Dit zegt niet in dat een account of profiel ook werkelijk bestaat!
     * @param $uid
     * @return bool
     */
    public static function isValidUid($uid)
    {
        return is_string($uid) && preg_match('/^[a-z0-9]{4}$/', $uid);
    }

    /**
     * @param string $uid
     *
     * @return bool
     */
    public static function existsUid($uid)
    {
        return ContainerFacade::getContainer()->get(AccountRepository::class)->find($uid) != null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function existsUsername($name)
    {
        return $this->findOneBy(['username' => $name]) != null;
    }

    public function findAdmins()
    {
        return $this->createQueryBuilder('a')
            ->where('a.perm_role NOT IN (:admin_perm_roles)')
            ->setParameter('admin_perm_roles', [AccessRole::Lid, AccessRole::Nobody, AccessRole::Eter, AccessRole::Oudlid])
            ->getQuery()->getResult();
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

            $this->_em->persist($menuItem);
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
        $this->_em->persist($account);
        $this->_em->flush();
        return $account;
    }

    /**
     * Verify SSHA hash.
     *
     * @param UserInterface $account
     * @param string $passPlain
     * @return boolean
     */
    public function controleerWachtwoord(UserInterface $account, $passPlain)
    {
        // Controleer of het wachtwoord klopt
        return $this->passwordHasherFactory->getPasswordHasher($account)
            ->verify($account->getPassword(), $passPlain, $account->getSalt());
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
    public function wijzigWachtwoord(Account $account, $passPlain, bool $isVeranderd = true)
    {
        if ($passPlain != '') {
            $account->pass_hash = $this->maakWachtwoord($account, $passPlain);
            if ($isVeranderd) {
                $account->pass_since = date_create_immutable();
            }
        }
        $this->_em->persist($account);
        $this->_em->flush();

        if ($isVeranderd) {
            // Sync LDAP
            $profiel = $account->profiel;
            if ($profiel) {
                $profiel->email = $account->email;
                ContainerFacade::getContainer()->get(ProfielRepository::class)->update($profiel);
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
    public function maakWachtwoord(Account $account, $passPlain)
    {
        return $this->passwordHasherFactory->getPasswordHasher($account)->hash($passPlain, $account->getSalt());
    }

    /**
     * @param Account $account
     */
    public function resetPrivateToken(Account $account)
    {
        $account->private_token = crypto_rand_token(150);
        $account->private_token_since = date_create_immutable();
        $this->_em->persist($account);
        $this->_em->flush();
    }

    /**
     * @param Account $account
     *
     * @return int
     */
    public function moetWachten(Account $account)
    {
        /**
         * @source OWASP best-practice
         */
        switch ($account->failed_login_attempts) {
            case 0:
                $wacht = 0;
                break;
            case 1:
                $wacht = 5;
                break;
            case 2:
                $wacht = 15;
                break;
            default:
                $wacht = 45;
                break;
        }
        if ($account->last_login_attempt == null) {
            return 0;
        }
        $diff = $account->last_login_attempt->getTimestamp() + $wacht - time();
        if ($diff > 0) {
            return $diff;
        }
        return 0;
    }

    /**
     * @param Account $account
     */
    public function failedLoginAttempt(Account $account)
    {
        $account->failed_login_attempts++;
        $account->last_login_attempt = date_create_immutable();
        $this->_em->persist($account);
        $this->_em->flush();
    }

    /**
     * @param Account $account
     */
    public function successfulLoginAttempt(Account $account)
    {
        $account->failed_login_attempts = 0;
        $account->last_login_attempt = date_create_immutable();
        $account->last_login_success = date_create_immutable();
        $this->_em->persist($account);
        $this->_em->flush();
    }

    public function delete(Account $account)
    {
        $this->_em->remove($account);
        $this->_em->flush();
    }

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        $user->pass_hash = $newEncodedPassword;

        $this->_em->flush();
        $this->_em->clear();
    }

    public function loadUserByUsername(string $username)
    {
        return $this->findOneByUsername($username);
    }

    public function findOneByUsername($username)
    {
        return $this->find($username)
            ?? $this->findOneBy(['username' => $username])
            ?? $this->findOneByEmail($username);
    }

    /**
     * @param $email
     * @return Account|null
     */
    public function findOneByEmail($email)
    {
        if (empty($email)) {
            return null;
        }

        return $this->findOneBy(['email' => $email]);
    }
}
