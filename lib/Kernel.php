<?php


namespace CsrDelft;

use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\view\bbcode\prosemirror\Mark;
use CsrDelft\view\bbcode\prosemirror\Node;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Configureer waar configuratie bestanden te vinden zijn.
 */
class Kernel extends BaseKernel {
	use MicroKernelTrait;

	/**
	 * @param ContainerConfigurator $container
	 */
	protected function configureContainer(ContainerConfigurator $container) {
		$container->import('../config/{packages}/*.yaml');
		$container->import('../config/{packages}/' . $this->environment . '/**/*.yaml');
		$container->import('../config/{services}.yaml');
		$container->import('../config/{services}_' . $this->environment . '.yaml');


		// We willen dat alles ook werkt als Memcache niet bestaat
		if (class_exists("Memcached") && $_ENV['MEMCACHED_URL'] != "") {
			$container->import('../config/custom/memcache.yaml');
		}
	}

	/**
	 * @param RoutingConfigurator $routes
	 */
	protected function configureRoutes(RoutingConfigurator $routes) {
		$routes->import('../config/{routes}/' . $this->environment . '/**/*.yaml');
		$routes->import('../config/{routes}/*.yaml');
		$routes->import('../config/{routes}.yaml');
	}

	protected function build(ContainerBuilder $builder) {
		$builder->registerForAutoconfiguration(FormulierTypeInterface::class)->addTag('csr.formulier.type');
		$builder->registerForAutoconfiguration(Mark::class)->addTag('csr.editor.mark');
		$builder->registerForAutoconfiguration(Node::class)->addTag('csr.editor.node');
	}
}
