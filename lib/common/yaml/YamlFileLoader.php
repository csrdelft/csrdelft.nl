<?php

namespace CsrDelft\common\yaml;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 14/07/2019
 */
class YamlFileLoader extends FileLoader
{
	public function load($resource, string $type = null): mixed
	{
		return Yaml::parse(file_get_contents($resource));
	}

	public function supports($resource, string $type = null): bool
	{
		return is_string($resource) &&
			'yaml' === pathinfo($resource, PATHINFO_EXTENSION);
	}
}
