<?php


namespace CsrDelft\Twig\Extension;


use CsrDelft\entity\groepen\BestuursLid;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\groepen\leden\BestuursLedenRepository;
use CsrDelft\repository\groepen\leden\CommissieLedenRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
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
	 * @var BestuursLedenRepository
	 */
	private $bestuursLedenRepository;
	/**
	 * @var CommissieLedenRepository
	 */
	private $commissieLedenRepository;
	/**
	 * @var LoginService
	 */
	private $loginService;

	public function __construct(
		LoginService $loginService,
		SuService $suService,
		BestuursLedenRepository $bestuursLedenRepository,
		CommissieLedenRepository $commissieLedenRepository
	)
	{
		$this->suService = $suService;
		$this->bestuursLedenRepository = $bestuursLedenRepository;
		$this->commissieLedenRepository = $commissieLedenRepository;
		$this->loginService = $loginService;
	}

	public function getFilters()
	{
		return [
			new TwigFilter('may_su_to', [$this, 'may_su_to']),
		];
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('mag', [$this, 'mag']),
			new TwigFunction('getBestuurslid', [$this, 'getBestuurslid']),
			new TwigFunction('getCommissielid', [$this, 'getCommissielid']),
		];
	}

	/**
	 * Mag de op dit moment ingelogde gebruiker $permissie?
	 *
	 * @param string $permission
	 * @param array|null $allowedAuthenticationMethods
	 * @return bool
	 */
	public function mag($permission, array $allowedAuthenticationMethods = null)
	{
		return $this->loginService->_mag($permission, $allowedAuthenticationMethods);
	}

	public function may_su_to(Account $account)
	{
		return $this->suService->maySuTo($account);
	}

	/**
	 * @param Profiel $profiel
	 * @return BestuursLid
	 */
	public function getBestuurslid(Profiel $profiel)
	{
		return $this->bestuursLedenRepository->findOneBy(['uid' => $profiel->uid]);
	}

	public function getCommissielid(Profiel $profiel)
	{
		return $this->commissieLedenRepository->findBy(['uid' => $profiel->uid], ['lid_sinds' => 'DESC']);
	}

}
