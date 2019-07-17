<?php

namespace CsrDelft\model\instellingen;

use CsrDelft\Orm\Entity\T;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/07/2019
 */
class InstellingConfiguration implements ConfigurationInterface {

	/**
	 * Generates the configuration tree builder.
	 *
	 * @return TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('instellingen');
		$rootNode
			->useAttributeAsKey('')
			->arrayPrototype()
			->useAttributeAsKey('', true)
			->arrayPrototype()
			->beforeNormalization()
			->ifString()
			->then(function ($v) {
				return ['default' => $v];
			})
			->end()
				->validate()
					->ifTrue(function($options) {
						if (is_string($options)) {
							return false;
						} elseif ($options['type'] == 'Enumeration') {
							return (!isset($options['opties'][$options['default']]) && !in_array($options['default'], $options['opties']));
						} else {
							return false;
						}
					})
					->thenInvalid('%s default must be in options')
			->end()
			->children()
			->scalarNode('default')->defaultNull()->end()
			->scalarNode('titel')->defaultNull()->end()
			->scalarNode('type')
			->defaultValue('String')
					->validate()
						->ifTrue(function($type) {
							return !(@constant(T::class . '::' . $type) !== null);
						})
						->thenInvalid('type %s is not in T.')
			->end()
			->end()
			->arrayNode('opties')->scalarPrototype()->end()->end()
			->scalarNode('beschrijving')->defaultValue('')->end()
			->end();

		return $treeBuilder;
	}
}
