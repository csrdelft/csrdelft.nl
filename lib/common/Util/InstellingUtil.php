<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\instellingen\InstellingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\instellingen\LidToestemmingRepository;

final class InstellingUtil
{
	/**
	 * @param Profiel $profiel
	 * @param string|string[] $key
	 * @param string $cat
	 * @param string $uitzondering Sommige commissie mogen wel dit veld zien.
	 * @return bool
	 */
	public static function is_zichtbaar($profiel, $key, $cat = 'profiel', $uitzondering = P_LEDEN_MOD): bool {
		$lidToestemmingRepository = ContainerFacade::getContainer()->get(
			LidToestemmingRepository::class
		);
		if (is_array($key)) {
			foreach ($key as $item) {
				if (
					!$lidToestemmingRepository->toestemming(
						$profiel,
						$item,
						$cat,
						$uitzondering
					)
				) {
					return false;
				}
			}

			return true;
		}

		return $lidToestemmingRepository->toestemming(
			$profiel,
			$key,
			$cat,
			$uitzondering
		);
	}

	/**
	 * @param $module
	 * @param $key
	 * @return string
	 */
	public static function lid_instelling($module, $key)
	{
		return ContainerFacade::getContainer()
			->get(LidInstellingenRepository::class)
			->getValue($module, $key);
	}

	/**
	 * @param $module
	 * @param $key
	 * @return string
	 */
	public static function instelling($module, $key)
	{
		return ContainerFacade::getContainer()
			->get(InstellingenRepository::class)
			->getValue($module, $key);
	}
}
