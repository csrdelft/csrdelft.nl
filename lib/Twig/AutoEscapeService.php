<?php

namespace CsrDelft\Twig;

use Twig\FileExtensionEscapingStrategy;

class AutoEscapeService
{
	const STRATEGY_ICAL = 'ical';
	const STRATEGY_XML = 'xml';
	const STRATEGY_MAIL = 'mail';
}
