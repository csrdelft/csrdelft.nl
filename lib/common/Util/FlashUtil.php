<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\view\Icon;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use function CsrDelft\common\CsrException;

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
		/** @var RequestStack */
		$requestStack = ContainerFacade::getContainer()
			->get('request_stack');

		$session = $requestStack->getSession();

		$level = match ($lvl) {
			-1 => 'danger',
			0 => 'info',
			1 => 'success',
			2 => 'warning',
			default => null
		};

		$msg = trim($msg);
		if (
			!empty($msg) && $level !== null && $session instanceof FlashBagAwareSessionInterface
		) {
			$session->getFlashBag()->add($level, $msg);
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
		/** @var RequestStack */
		$requestStack = ContainerFacade::getContainer()
			->get('request_stack');

		$session = $requestStack->getSession();

		if (!$session instanceof FlashBagAwareSessionInterface) {
			throw new CsrException("Geen flash bag");
		}

		$flashes = $session->getFlashBag()->all();
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
				$msgSafe = $type == 'html' ? $msg : htmlentities((string) $msg);

				$melding .= <<<HTML
<div class="alert alert-$type">
{$icon}$msgSafe
</div>
HTML;
			}
		}

		return '<div id="melding">' . $melding . '</div>';
	}
}
