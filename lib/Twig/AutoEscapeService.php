<?php


namespace CsrDelft\Twig;


use Twig\FileExtensionEscapingStrategy;

class AutoEscapeService {
	const STRATEGY_ICAL = 'ical';
	const STRATEGY_XML = 'xml';

	public function guess($name) {
		if (endsWith($name, '.ical.twig')) {
			return self::STRATEGY_ICAL;
		}
		if (endsWith($name, '.xml.twig')) {
			return self::STRATEGY_XML;
		}

		return FileExtensionEscapingStrategy::guess($name);
	}

}
