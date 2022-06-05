<?php

namespace CsrDelft\common\yaml;

use CsrDelft\command\FlushMemcacheCommand;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\FileLocator;

/**
 * Lees een yaml bestand uit.
 *
 * Standaard bestand heeft op het eerste niveau een beschrijving, dan een niveau met categorieen
 * en binnen iedere categorie specifieke velden.
 *
 * Cached de settings op schijf in productie mode.
 *
 * @see FlushMemcacheCommand voor Cache invalidation
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/07/2019
 */
trait YamlInstellingen
{
	private $defaults;

	/**
	 * @param string $resource
	 * @param ConfigurationInterface $configuration
	 * @throws FileLoaderImportCircularReferenceException
	 * @throws LoaderLoadException
	 */
	protected function load($resource, $configuration)
	{
		$file = CONFIG_CACHE_PATH . str_replace('/', '_', $resource) . '.cache.php';

		/** @noinspection PhpIncludeInspection */
		$config = @include $file;

		// Config niet eerder geladen of in debug mode.
		if (DEBUG || $config == null) {
			$yamlLoader = new YamlFileLoader(new FileLocator([CONFIG_PATH]));
			$yamlLoader->setCurrentDir(__DIR__);
			$yaml = $yamlLoader->import($resource);

			$config = (new Processor())->processConfiguration($configuration, $yaml);

			$this->writeConfig($config, $file);
		}

		$this->defaults = $config;
	}

	public function hasKey($module, $key)
	{
		return isset($this->defaults[$module][$key]);
	}

	public function getDefinition($module, $key)
	{
		return $this->defaults[$module][$key];
	}

	public function getField($module, $key, $field)
	{
		return $this->defaults[$module][$key][$field];
	}

	/**
	 * @return string[]
	 */
	public function getAll()
	{
		return $this->defaults;
	}

	public function getModules()
	{
		return array_keys($this->defaults);
	}

	public function getModuleKeys($module)
	{
		return array_keys($this->defaults[$module]);
	}

	private function writeConfig($config, $file)
	{
		if (!file_exists($file)) {
			@mkdir(CONFIG_CACHE_PATH, 0777, true);
			touch($file);
		}
		/**
		 * Deze config is direct van schijf gelezen en bevat geen informatie die beinvloedbaar is door gebruikers.
		 */
		file_put_contents($file, '<?php return ' . var_export($config, true) . ';');
	}
}
