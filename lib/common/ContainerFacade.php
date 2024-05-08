<?php

namespace CsrDelft\common;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Geeft toegang tot de Symfony Service Container.
 *
 * Gebruik alleen als er geen betere manier is om services te pakken te krijgen.
 * @deprecated Gebruik de service container
 *
 * @package CsrDelft\common
 */
class ContainerFacade
{
	/** @var ContainerInterface */
	private static $container;

	public static function init(ContainerInterface $container)
	{
		static::$container = $container;
	}

	/**
	 * @return ContainerInterface
	 */
	public static function getContainer()
	{
		if (!static::$container) {
			throw new CsrException('Container niet geinitialiseerd');
		}

		return static::$container;
	}
}
