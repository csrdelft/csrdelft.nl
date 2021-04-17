<?php


namespace CsrDelft\Twig\Extension;


use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
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
	 * @var LoginService
	 */
	private $loginService;

	public function __construct(
		LoginService $loginService,
		SuService $suService
	)
	{
		$this->suService = $suService;
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
	 * @return GroepLid
	 */
	public function getBestuurslid(Profiel $profiel)
	{
		// TODO: Fixme
		return null;
	}

	public function getCommissielid(Profiel $profiel)
	{
		// TODO: Fixme
		return null;
	}

}
