<?php

namespace CsrDelft;

use CsrDelft\Component\DataTable\DataTableTypeInterface;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\Node;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Cache\LockRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Configureer waar configuratie bestanden te vinden zijn.
 */
class Kernel extends BaseKernel
{
	use MicroKernelTrait;

	public function __construct(string $environment, bool $debug)
	{
		parent::__construct($environment, $debug);

		// Gebruik geen cache stampede beveiliging
		// https://github.com/csrdelft/csrdelft.nl/issues/948
		LockRegistry::setFiles([]);
	}

	/**
	 * @param RoutingConfigurator $routes
	 */
	protected function configureRoutes(RoutingConfigurator $routes): void
	{
		$routes->import('../config/{routes}/' . $this->environment . '/**/*.yaml');
		$routes->import('../config/{routes}/*.yaml');
		$routes->import('../config/{routes}.yaml');
	}

	/**
	 * @return void
	 */
	protected function build(ContainerBuilder $builder)
	{
		$builder
			->registerForAutoconfiguration(FormulierTypeInterface::class)
			->addTag('csr.formulier.type');
		$builder
			->registerForAutoconfiguration(Mark::class)
			->addTag('csr.editor.mark');
		$builder
			->registerForAutoconfiguration(Node::class)
			->addTag('csr.editor.node');
		$builder
			->registerForAutoconfiguration(DataTableTypeInterface::class)
			->addTag('csr.table.type');
	}
}
