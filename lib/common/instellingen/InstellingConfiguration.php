<?php

namespace CsrDelft\common\instellingen;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/07/2019
 */
class InstellingConfiguration implements ConfigurationInterface
{
	const FIELD_DEFAULT = 'default';
	const FIELD_TITEL = 'titel';
	const FIELD_TYPE = 'type';
	const FIELD_OPTIES = 'opties';
	const FIELD_BESCHRIJVING = 'beschrijving';

	/**
	 * Generates the configuration tree builder.
	 *
	 * @return TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder('instellingen');
		$rootNode = $treeBuilder->getRootNode();
		$rootNode
			->useAttributeAsKey('')
			->arrayPrototype()
			->useAttributeAsKey('', true)
			->arrayPrototype()
			->beforeNormalization()
			->ifString()
			->then(fn($v) => [self::FIELD_DEFAULT => $v])
			->end()
			->validate()
			->ifTrue(function ($options) {
				if (is_string($options)) {
					return false;
				} elseif ($options[self::FIELD_TYPE] == InstellingType::Enumeration) {
					return !isset(
						$options[self::FIELD_OPTIES][$options[self::FIELD_DEFAULT]]
					) &&
						!in_array(
							$options[self::FIELD_DEFAULT],
							$options[self::FIELD_OPTIES]
						);
				} else {
					return false;
				}
			})
			->thenInvalid('%s default must be in options')
			->end()
			->children()
			->scalarNode(self::FIELD_DEFAULT)
			->defaultNull()
			->end()
			->scalarNode(self::FIELD_TITEL)
			->defaultNull()
			->end()
			->scalarNode(self::FIELD_TYPE)
			->defaultValue(InstellingType::String)
			->validate()
			->ifTrue(fn($type) => !isset(InstellingType::getTypeOptions()[$type]))
			->thenInvalid('type %s is not in T.')
			->end()
			->end()
			->arrayNode(self::FIELD_OPTIES)
			->scalarPrototype()
			->end()
			->end()
			->scalarNode(self::FIELD_BESCHRIJVING)
			->defaultValue('')
			->end()
			->end();

		return $treeBuilder;
	}
}
