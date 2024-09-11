<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\entity\security\Account;
use CsrDelft\repository\groepen\BesturenRepository;
use CsrDelft\repository\groepen\CommissiesRepository;
use CsrDelft\service\security\SuService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AccountTwigExtension extends AbstractExtension
{
	public function __construct(
		private readonly BesturenRepository $besturenRepository,
		private readonly CommissiesRepository $commissiesRepository,
		private readonly SuService $suService
	) {
	}

	public function getFilters()
	{
		return [new TwigFilter('may_su_to', $this->may_su_to(...))];
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('getBestuurslid', $this->getBestuurslid(...)),
			new TwigFunction('getCommissielid', $this->getCommissielid(...)),
		];
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
}
