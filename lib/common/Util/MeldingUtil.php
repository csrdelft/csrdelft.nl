<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\Icon;

final class MeldingUtil
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
	 * @see    getMelding()
	 * gebaseerd op DokuWiki code
	 * @param string $msg
	 * @param int $lvl
	 */
	public static function setMelding(string $msg, int $lvl)
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
	 */
	public static function getMelding()
	{
		$flashBag = ContainerFacade::getContainer()
			->get('session')
			->getFlashBag();

		$melding = '';
		foreach ($flashBag->all() as $type => $meldingen) {
			foreach ($meldingen as $msg) {
				$melding .= static::formatMelding($msg, $type);
			}
		}

		return '<div id="melding">' . $melding . '</div>';
	}

	/**
	 * @param string $msg
	 * @param string $lvl
	 * @return string
	 */
	private static function formatMelding(string $msg, string $lvl)
	{
		$icon = Icon::getTag('alert-' . $lvl);

		return <<<HTML
<div class="alert alert-${lvl}">
${icon}${msg}
</div>
HTML;
	}
}
