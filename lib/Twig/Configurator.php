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
		$environment->getExtension(EscaperExtension::class)->setEscaper(AutoEscapeService::STRATEGY_ICAL, [$this, 'escape_ical']);

		$this->configurator->configure($environment);
	}

	public function escape_ical($twig, $string, $charset)
	{
		return escape_ical($string);
	}
}

