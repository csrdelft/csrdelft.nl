<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\repository\groepen\CommissiesRepository;
use CsrDelft\service\GoogleSync;
use CsrDelft\service\security\SuService;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AccountTwigExtension extends AbstractExtension
{
	/**
	 * @var SuService
	 */
	private $suService;
	/**
	 * @var BesturenRepository
	 */
	private $besturenRepository;
	/**
	 * @var CommissiesRepository
	 */
	private $commissiesRepository;
	/**
	 * @var GoogleSync
	 */
	private $googleSync;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(
		Security $security,
		BesturenRepository $besturenRepository,
		CommissiesRepository $commissiesRepository,
		GoogleSync $googleSync,
		SuService $suService
	) {
		$this->suService = $suService;
		$this->besturenRepository = $besturenRepository;
		$this->commissiesRepository = $commissiesRepository;
		$this->googleSync = $googleSync;
		$this->security = $security;
	}

	public function getFilters()
	{
		return [new TwigFilter('may_su_to', [$this, 'may_su_to'])];
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('mag', [$this, 'mag']),
			new TwigFunction('getBestuurslid', [$this, 'getBestuurslid']),
			new TwigFunction('getCommissielid', [$this, 'getCommissielid']),
			new TwigFunction('isInGoogleContacts', [$this, 'isInGoogleContacts']),
		];
	}

	/**
	 * Mag de op dit moment ingelogde gebruiker $permissie?
	 *
	 * @param string $permission
	 * @return bool
	 */
	public function mag($permission)
	{
		$permission = preg_replace('/^P_/', 'ROLE_', $permission);
		return $this->security->isGranted($permission);
	}

	public function may_su_to(Account $account)
	{
		return $this->suService->maySuTo($account);
	}

	/**
	 * @param Profiel $profiel
	 * @return GroepLid|null
	 */
	public function getBestuurslid(Profiel $profiel)
	{
		$besturen = $this->besturenRepository->getGroepenVoorLid($profiel, [
			GroepStatus::OT,
			GroepStatus::HT,
			GroepStatus::FT,
		]);
		if (count($besturen)) {
			return $besturen[0]->getLid($profiel->uid);
		}
		return null;
	}

	/**
	 * @param Profiel $profiel
	 * @return GroepLid[]|\Generator
	 */
	public function getCommissielid(Profiel $profiel)
	{
		$commissies = $this->commissiesRepository->getGroepenVoorLid($profiel);
		foreach ($commissies as $commissie) {
			yield $commissie->getLid($profiel->uid);
		}
	}

	public function isInGoogleContacts(Profiel $profiel): bool
	{
		try {
			if (!$this->googleSync->isAuthenticated()) {
				return false;
			}
			$this->googleSync->init();
			return !is_null($this->googleSync->existsInGoogleContacts($profiel));
		} catch (CsrGebruikerException $e) {
			setMelding($e->getMessage(), 0);
		} catch (RequestException $e) {
			setMelding($e->getMessage(), -1);
		}
		return false;
	}
}
