<?php

namespace CsrDelft\common\Util;

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
		$levels[-1] = 'danger';
		$levels[0] = 'info';
		$levels[1] = 'success';
		$levels[2] = 'warning';
		$msg = trim($msg);
		if (
			!empty($msg) &&
			($lvl === -1 || $lvl === 0 || $lvl === 1 || $lvl === 2)
		) {
			if (!isset($_SESSION['melding'])) {
				$_SESSION['melding'] = [];
			}
			// gooit verouderde gegevens weg
			if (is_string($_SESSION['melding'])) {
				$_SESSION['melding'] = [];
			}
			$_SESSION['melding'][] = ['lvl' => $levels[$lvl], 'msg' => $msg];
		}
	}

	/**
	 * Geeft berichten weer die opgeslagen zijn in de sessie met met MeldingUtil::setMelding($msg, $lvl)
	 *
	 * @return string html van melding(en) of lege string
	 */
	public static function getMelding()
	{
		if (isset($_SESSION['melding']) && is_array($_SESSION['melding'])) {
			$melding = '';
			foreach ($_SESSION['melding'] as $msg) {
				$melding .= static::formatMelding($msg['msg'], $msg['lvl']);
			}
			// de melding maar één keer tonen.
			unset($_SESSION['melding']);
		} else {
			$melding = '';
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
