<?php


namespace CsrDelft\Twig;


use Twig\FileExtensionEscapingStrategy;

class AutoEscapeService {
	const STRATEGY_ICAL = 'ical';
	const STRATEGY_XML = 'xml';
	const STRATEGY_MAIL = 'mail';

	public function guess($name) {
		if (endsWith($name, '.ical.twig')) {
			return self::STRATEGY_ICAL;
		}
		if (endsWith($name, '.xml.twig')) {
			return self::STRATEGY_XML;
		}
		if (endsWith($name, '.mail.twig')) {
			return self::STRATEGY_MAIL;
		}

		return FileExtensionEscapingStrategy::guess($name);
	}

}
