<?php

namespace CsrDelft\Twig\Extension;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use CsrDelft\view\toestemming\ToestemmingModalForm;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class InstellingenTwigExtension extends AbstractExtension
{
	/**
	 * @var LidInstellingenRepository
	 */
	private $lidInstellingenRepository;
	/**
	 * @var InstellingenRepository
	 */
	private $instellingenRepository;
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;

	public function __construct(
		LidInstellingenRepository $lidInstellingenRepository,
		InstellingenRepository    $instellingenRepository,
		LidToestemmingRepository  $lidToestemmingRepository
	)
	{
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->instellingenRepository = $instellingenRepository;
		$this->lidToestemmingRepository = $lidToestemmingRepository;
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('instelling', [$this, 'instelling']),
			new TwigFunction('lid_instelling', [$this, 'lid_instelling']),
			new TwigFunction('toestemming_gegeven', [$this, 'toestemming_gegeven']),
			new TwigFunction('toestemming_form', [$this, 'toestemming_form']),
		];
	}

	public function getFilters()
	{
		return [
			new TwigFilter('is_zichtbaar', [$this, 'is_zichtbaar']),
		];
	}

	public function lid_instelling($module, $key)
	{
		return $this->lidInstellingenRepository->getValue($module, $key);
	}

	public function instelling($module, $key)
	{
		return $this->instellingenRepository->getValue($module, $key);
	}

	public function toestemming_gegeven()
	{
		return $this->lidToestemmingRepository->toestemmingGegeven();
	}

	public function toestemming_form()
	{
		return new ToestemmingModalForm($this->lidToestemmingRepository);
	}

	/**
	 * @param Profiel $profiel
	 * @param string|string[] $key
	 * @param string $cat
	 * @param string $uitzondering Sommige commissie mogen wel dit veld zien.
	 * @return bool
	 */
	public function is_zichtbaar($profiel, $key, $cat = 'profiel', $uitzondering = P_LEDEN_MOD)
	{
		if (is_array($key)) {
			foreach ($key as $item) {
				if (!$this->lidToestemmingRepository->toestemming($profiel, $item, $cat, $uitzondering)) {
					return false;
				}
			}

			return true;
		}

		return $this->lidToestemmingRepository->toestemming($profiel, $key, $cat, $uitzondering);
	}
}
