<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\Icon;

final class FlashUtil
{
	/**
	 * Stores a message.
	 *
	 * Levels can be:
	 *
	 * -1 error / danger
	 *  0 info
	 *  1 success
	 *  2 warning / notify
	 *
	 * @param string $msg
	 * @param int $lvl
	 * @see    getFlashUsingContainerFacade()
	 * @deprecated gebruik FlashBag
	 */
	public static function setFlashWithContainerFacade(string $msg, int $lvl)
	{
		$flashBag = ContainerFacade::getContainer()
			->get('session')
			->getFlashBag();

		$levels[-1] = 'danger';
		$levels[0] = 'info';
		$levels[1] = 'success';
		$levels[2] = 'warning';
		$msg = trim($msg);
		if (
			!empty($msg) &&
			($lvl === -1 || $lvl === 0 || $lvl === 1 || $lvl === 2)
		) {
			$flashBag->add($levels[$lvl], $msg);
		}
	}

	/**
	 * Geeft berichten weer die opgeslagen zijn in de sessie met met MeldingUtil::setMelding($msg, $lvl)
	 *
	 * @return string html van melding(en) of lege string
	 * @deprecated Gebruik FlashBag direct of een twig template
	 * @see melding.html.twig
	 */
	public static function getFlashUsingContainerFacade()
	{
		$flashBag = ContainerFacade::getContainer()
			->get('session')
			->getFlashBag();

		$flashes = $flashBag->all();
		return self::format($flashes);
	}

	/**
	 * @param array $flashes
	 * @return string
	 */
	public static function format(array $flashes): string
	{
		$melding = '';
		foreach ($flashes as $type => $meldingen) {
			foreach ($meldingen as $msg) {
				$icon = Icon::getTag('alert-' . $type);

				$melding .= <<<HTML
<div class="alert alert-$type">
${icon}$msg
</div>
HTML;
			}
		}

		return '<div id="melding">' . $melding . '</div>';
	}
}
