<?php


namespace CsrDelft\Twig;


use Twig\FileExtensionEscapingStrategy;

class AutoEscapeService {
	const STRATEGY_ICAL = 'ical';

	public function guess($name) {
		if ($name == '.ical.twig') {
			return self::STRATEGY_ICAL;
		}

		return FileExtensionEscapingStrategy::guess($name);
	}

}
