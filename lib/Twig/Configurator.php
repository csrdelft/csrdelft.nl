<?php

namespace CsrDelft\Twig;

use Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\EnvironmentConfigurator;
use Twig\Environment;
use Twig\Extension\EscaperExtension;

class Configurator
{
	/**
	 * @var EnvironmentConfigurator
	 */
	private $configurator;

	public function __construct(EnvironmentConfigurator $configurator)
	{
		$this->configurator = $configurator;
	}

	public function configure(Environment $environment)
	{
		$environment
			->getExtension(EscaperExtension::class)
			->setEscaper(AutoEscapeService::STRATEGY_ICAL, [$this, 'escape_ical']);
		$environment
			->getExtension(EscaperExtension::class)
			->setEscaper(AutoEscapeService::STRATEGY_XML, [$this, 'escape_xml']);
		$environment
			->getExtension(EscaperExtension::class)
			->setEscaper(AutoEscapeService::STRATEGY_MAIL, [$this, 'escape_mail']);

		$this->configurator->configure($environment);
	}

	public function escape_ical($twig, $string, $charset)
	{
		return escape_ical($string);
	}

	public function escape_xml($twig, $string, $charset)
	{
		return htmlspecialchars($string, ENT_XML1, 'UTF-8');
	}

	public function escape_mail($twig, $string, $charset)
	{
		return $string;
	}
}
