<?php

namespace CsrDelft\model;

use CsrDelft\common\CsrException;
use CsrDelft\Orm\PersistenceModel;

/**
 * Maak het mogelijk om berekende attributen te definieren. Op deze manier kun je logica achter een attribuut hangen.
 *
 * @package CsrDelft\model
 */
trait ComputedAttribute {
	/** @var array cache voor deze request */
	private static $computed_attribute_cache = [];

	public function __get($name) {
		if (isset(static::$computed_attributes[$name])) {
			$definition = static::$computed_attributes[$name];

			if ($definition[0] == T2::ForeignKey) {
				return $this->retrieveForeignKey($definition);
			}

			return call_user_func_array([static::class, 'get' . ucfirst($this->toCamelCase($name))], []);
		} else {
			return parent::__get($name);
		}
	}

	public function getComputedAttributes() {
		return array_keys(static::$computed_attributes);
	}

	public function getComputedAttributeDefinition($name) {
		return static::$computed_attributes[$name];
	}

	public function jsonSerialize() {
		$arr = parent::jsonSerialize();

		$computedProperties = [];
		foreach (static::$computed_attributes as $attribute => $definition) {
			$computedProperties[$attribute] = $this->{$attribute};
		}

		return array_merge($arr, $computedProperties);
	}

	private function toCamelCase($name) {
		return preg_replace_callback('/_([a-zA-Z])/', function ($matches) {
			return strtoupper($matches[1]);
		}, $name);
	}

	/**
	 * @param array $definition
	 * @return mixed
	 */
	private function retrieveForeignKey($definition) {
		if (count($definition) != 2) throw new CsrException('Verwacht een definitie met lengte 2');

		$foreignModel = $definition[1];

		if (!is_a($foreignModel,PersistenceModel::class)) throw new CsrException('Verwacht een PersistenceModel in ForeignKey definitie kreeg: ' . $foreignModel);

		/** @var PersistenceModel $foreignModel */
		$foreignEntityClass = $foreignModel::ORM;

		if (!isset(class_uses($foreignEntityClass)[HasForeignKeys::class])) throw new CsrException('ForeignKey in computed attribute verwijst niet naar entity met foreign keys: ' . $foreignEntityClass);

		/** @var HasForeignKeys $foreignEntityClass */
		$foreignKey = array_search(static::class, $foreignEntityClass::getForeignKeys());
		$primaryKey = $this->getPrimaryKey();

		if (count($primaryKey) !== 1) throw new CsrException('Kan geen foreign key van model met multi-column key opzoeken: ' . $foreignEntityClass);

		$cacheKey = $foreignModel . $this->{$primaryKey[0]}; // unique-ish

		if (!isset(static::$computed_attribute_cache[$cacheKey])) {
			static::$computed_attribute_cache[$cacheKey] = $foreignModel::instance()->find($foreignKey . ' = ?', [$this->{$primaryKey[0]}])->fetchAll();
		}

		return static::$computed_attribute_cache[$cacheKey];
	}
}
