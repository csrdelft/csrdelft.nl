<?php

namespace CsrDelft\entity\profiel;

use CsrDelft\common\CsrException;
use CsrDelft\repository\instellingen\LidToestemmingRepository;
use ReflectionClass;
use ReflectionProperty;

/**
 * Bescherm velden van een Profiel object door eerst te controleren of de velden te bekijken zijn.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class ProfielToestemmingProxy extends Profiel
{
	/**
	 * @var Profiel
	 */
	private $profiel;
	/**
	 * @var LidToestemmingRepository
	 */
	private $lidToestemmingRepository;

	/**
	 * @var string[]
	 */
	private $filterVelden;

	/**
	 * @var string[]
	 */
	private static $publicVelden;

	public function __construct(
		Profiel $profiel,
		LidToestemmingRepository $lidToestemmingRepository
	) {
		parent::__construct();

		$this->profiel = $profiel;
		$this->lidToestemmingRepository = $lidToestemmingRepository;

		if (!static::$publicVelden) {
			$reflectionClass = new ReflectionClass(get_class($this));
			$publicReflectionProperties = $reflectionClass->getProperties(
				ReflectionProperty::IS_PUBLIC
			);
			$staticReflectionProperties = $reflectionClass->getProperties(
				ReflectionProperty::IS_STATIC
			);

			static::$publicVelden = array_map(function ($prop) {
				return $prop->name;
			}, array_diff($publicReflectionProperties, $staticReflectionProperties));
		}

		$this->filterVelden = $this->lidToestemmingRepository->getModuleKeys(
			'profiel'
		);

		// Strip alle velden uit dit object om alles via __get te sturen
		foreach (static::$publicVelden as $field) {
			unset($this->$field);
		}
	}

	private function zichtbaar(string $name)
	{
		return !in_array($name, $this->filterVelden) ||
			$this->lidToestemmingRepository->toestemming($this->profiel, $name);
	}

	public function __get(string $name)
	{
		if (! $this->zichtbaar($name)) {
			return null;
		}

		// Als profiel->get... bestaat, gebruik de getter
		// Voor compatibiliteit met twig, want daar is geen verschil tussen
		// een getter en een property.
		$getter = 'get' . ucfirst($name);
		if (method_exists($this, $getter)) {
			return $this->profiel->{$getter}();
		}

		return $this->zichtbaar($name) ? $this->profiel->$name : null;
	}

	public function __isset(string $name)
	{
		return $this->zichtbaar($name);
	}

	public function __set($name, $value)
	{
		throw new CsrException('Kan geen velden zetten op ProfielToestemmingProxy');
	}
}
