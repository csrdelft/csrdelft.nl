<?php

namespace CsrDelft\Twig;

use CsrDelft\common\Util\TextUtil;
use Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\EnvironmentConfigurator;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

class Configurator
{
	public function __construct(
		private readonly EnvironmentConfigurator $configurator
	) {
	}

	public function configure(Environment $environment): void
	{
		$environment
			->getRuntime(EscaperRuntime::class)
			->setEscaper(AutoEscapeService::STRATEGY_ICAL, $this->escape_ical(...));
		$environment
			->getRuntime(EscaperRuntime::class)
			->setEscaper(AutoEscapeService::STRATEGY_XML, $this->escape_xml(...));
		$environment
			->getRuntime(EscaperRuntime::class)
			->setEscaper(AutoEscapeService::STRATEGY_MAIL, $this->escape_mail(...));

		$this->configurator->configure($environment);
	}

	public function escape_ical(string $string, string $charset): string
	{
		return TextUtil::escape_ical($string);
	}

	public function escape_xml(string $string, string $charset): string
	{
		return htmlspecialchars($string, ENT_XML1, 'UTF-8');
	}

	public function escape_mail(string $string, string $charset): string
	{
		return $string;
	}
}
