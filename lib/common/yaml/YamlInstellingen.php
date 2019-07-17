<?php

namespace CsrDelft\common\yaml;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;

/**
 * Lees een yaml bestand uit.
 *
 * Standaard bestand heeft op het eerste niveau een beschrijving, dan een niveau met categorieen
 * en binnen iedere categorie specifieke velden.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/07/2019
 */
trait YamlInstellingen {
	private $defaults;

	/**
	 * @param string $resource
	 * @param ConfigurationInterface $configuration
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws FileLoaderLoadException
	 */
	protected function load($resource, $configuration) {
		$configDirectories = [CONFIG_PATH];

		$fileLocator = new FileLocator($configDirectories);
		$yamlLoader = new YamlFileLoader($fileLocator);
		$yamlLoader->setCurrentDir(__DIR__);
		$config = $yamlLoader->import($resource);

		$processor = new Processor();

		$this->defaults = $processor->processConfiguration($configuration, $config);
	}

	public function hasKey($module, $key) {
		return isset($this->defaults[$module][$key]);
	}

	public function getDefinition($module, $key) {
		return $this->defaults[$module][$key];
	}

	public function getField($module, $key, $field) {
		return $this->defaults[$module][$key][$field];
	}

	public function getAll() {
		return $this->defaults;
	}

	public function getModules() {
		return array_keys($this->defaults);
	}

	public function getModuleKeys($module) {
		return array_keys($this->defaults[$module]);
	}
}
