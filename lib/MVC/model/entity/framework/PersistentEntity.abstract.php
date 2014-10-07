<?php

require_once 'MVC/model/entity/framework/Sparse.interface.php';
require_once 'MVC/model/entity/framework/PersistentEnum.interface.php';
require_once 'MVC/model/entity/framework/PersistentType.enum.php';
require_once 'MVC/model/entity/framework/PersistentAttribute.class.php';

/**
 * PersistentEntity.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Requires static properties in superclass: $table_name, $persistent_attributes, $primary_key
 * Requires getters and setters for every sparse attribute to update: $attr_retrieved
 * 
 * @see documentation of PersistenceModel->retrieveAttributes() for usage of sparse attributes
 * 
 * Optional: static $rename_attributes = array('oldname' => 'newname');
 */
abstract class PersistentEntity implements Sparse, JsonSerializable {
	// N.B. non-static part below

	/**
	 * The names of attributes that should not be retrieved by default.
	 * @var array
	 */
	protected static $sparse_attributes;

	/**
	 * Static constructor is called (by inheritance) first and only from PersistenceModel.
	 */
	public static function __constructStatic() {
		/*
		 * Override this to extend the persistent attributes:
		 * 
		  parent::__constructStatic();
		  self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
		 * 
		 */

		/*
		 * Optional: run conversion code before checkTables()
		 */
	}

	public static function getTableName() {
		return static::$table_name;
	}

	/**
	 * Return the names of attributes that should be retrieved by default.
	 * 
	 * @return array
	 */
	public static function getNonSparseAttributes() {
		if (isset(static::$sparse_attributes)) {
			return array_diff(static::getAttributes(), static::$sparse_attributes);
		}
		return static::getAttributes();
	}

	/**
	 * Return the names of attributes that should NOT be retrieved by default.
	 * 
	 * @return array
	 */
	public static function getSparseAttributes() {
		if (isset(static::$sparse_attributes)) {
			return static::$sparse_attributes;
		}
		return array();
	}

	/**
	 * Get all attribute names.
	 * 
	 * @return array
	 */
	public static function getAttributes() {
		return array_keys(static::$persistent_attributes);
	}

	public static function getAttributeDefinition($attribute_name) {
		return static::$persistent_attributes[$attribute_name];
	}

	public static function getPrimaryKey() {
		return static::$primary_key;
	}

	/**
	 * To compare table description of MySQL.
	 * 
	 * @param string $name
	 * @param array $definition
	 * @return PersistentAttribute
	 */
	private static function makePersistentAttribute($name, array $definition) {
		$attribute = new PersistentAttribute();
		$attribute->field = $name;
		$attribute->type = $definition[0];
		$attribute->default = null;
		if (isset($definition[1]) AND $definition[1]) {
			$attribute->null = 'YES';
		} else {
			$attribute->null = 'NO';
		}
		$attribute->extra = (isset($definition[2]) ? $definition[2] : '');
		if ($attribute->type === T::Boolean) {
			$attribute->type = 'tinyint(1)';
		} elseif ($attribute->type === T::Integer) {
			$attribute->type = 'int(11)';
		} elseif ($attribute->type === T::String) {
			$attribute->type = 'varchar(255)';
		} elseif ($attribute->type === T::UID) {
			$attribute->type = 'varchar(4)';
		} elseif ($attribute->type === T::Char) {
			$attribute->type = 'char(1)';
		} elseif ($attribute->type === T::Enumeration) {
			$max = 0;
			$class = $attribute->extra;
			foreach ($class::getTypeOptions() as $option) {
				$max = max($max, strlen($option));
			}
			$attribute->type = 'varchar(' . $max . ')';
			$attribute->extra = '';
		}
		if (in_array($name, static::getPrimaryKey())) {
			$attribute->key = 'PRI';
		} else {
			$attribute->key = '';
		}
		return $attribute;
	}

	/**
	 * Check for differences in persistent attributes.
	 * 
	 * @unsupported INDEX check; FOREIGN KEY check;
	 */
	public static function checkTable() {
		$orm = get_called_class();
		$attributes = array();
		foreach (static::$persistent_attributes as $name => $definition) {
			$attributes[$name] = self::makePersistentAttribute($name, $definition);
		}
		try {
			$database_attributes = group_by_distinct('field', DatabaseAdmin::instance()->sqlDescribeTable(static::getTableName()));
		} catch (Exception $e) {
			if (endsWith($e->getMessage(), static::getTableName() . "' doesn't exist")) {
				DatabaseAdmin::instance()->sqlCreateTable(static::getTableName(), $attributes, static::getPrimaryKey());
				return;
			} else {
				throw $e; // rethrow to controller
			}
		}
		// Rename attributes
		if (property_exists($orm, 'rename_attributes')) {
			foreach (static::$rename_attributes as $oldname => $newname) {
				if (property_exists($orm, $newname)) {
					DatabaseAdmin::instance()->sqlChangeAttribute(static::getTableName(), $attributes[$newname], $oldname);
				}
			}
		}
		$previous_attribute = null;
		foreach (static::$persistent_attributes as $name => $definition) {
			// Add missing persistent attributes
			if (!array_key_exists($name, $database_attributes)) {
				if (!(property_exists($orm, 'rename_attributes') AND in_array($name, static::$rename_attributes))) {
					DatabaseAdmin::instance()->sqlAddAttribute(static::getTableName(), $attributes[$name], $previous_attribute);
				}
			} else {
				// Check exisiting persistent attributes for differences
				$diff = false;
				if ($attributes[$name]->type !== $database_attributes[$name]->type) {
					$diff = true;
				}
				if ($attributes[$name]->null !== $database_attributes[$name]->null) {
					$diff = true;
				}
				// Cast database value if default value is defined
				if ($attributes[$name]->default !== null) {
					if ($definition[0] === T::Boolean) {
						$database_attributes[$name]->default = (boolean) $database_attributes[$name]->default;
					} elseif ($definition[0] === T::Integer) {
						$database_attributes[$name]->default = (int) $database_attributes[$name]->default;
					} elseif ($definition[0] === T::Float) {
						$database_attributes[$name]->default = (float) $database_attributes[$name]->default;
					}
				}
				if ($attributes[$name]->default !== $database_attributes[$name]->default) {
					$diff = true;
				}
				if ($attributes[$name]->extra !== $database_attributes[$name]->extra) {
					$diff = true;
				}
				if ($attributes[$name]->key !== $database_attributes[$name]->key AND ! ($attributes[$name]->key === '' AND $database_attributes[$name]->key === 'MUL')) {
					$diff = true;
				}
				if ($diff) {
					DatabaseAdmin::instance()->sqlChangeAttribute(static::getTableName(), $attributes[$name]);
				}
			}
			$previous_attribute = $name;
		}
		// Remove non-persistent attributes
		foreach ($database_attributes as $name => $attribute) {
			if (!array_key_exists($name, static::$persistent_attributes) AND ! (property_exists($orm, 'rename_attributes') AND array_key_exists($name, static::$rename_attributes))) {
				DatabaseAdmin::instance()->sqlDeleteAttribute(static::getTableName(), $attribute);
			}
		}
	}

	/**
	 * The names of attributes that have been retrieved for this instance.
	 * Relies on getters and setters to update this.
	 * Used to discern values that have not been retrieved as these are invalid.
	 * @var array
	 */
	protected $attr_retrieved = array();

	/**
	 * Constructor is called late (after attributes are set)
	 * by PDO::FETCH_CLASS with $cast = true.
	 * 
	 * @param boolean $cast Regular construction should not cast unset properties!
	 */
	public function __construct($cast = false) {
		if ($cast) {
			$this->castValues();
		}
		if (isset(static::$sparse_attributes)) { // bookkeeping
			$this->attr_retrieved = $this->getNonSparseAttributes();
		}
	}

	public function getUUID() {
		return implode('.', $this->getValues(true)) . '@' . get_class($this) . '.csrdelft.nl';
	}

	public function jsonSerialize() {
		$array = (array) $this;
		// strip non-public attribute prefixes
		foreach ($array as $key => $value) {
			$pos = strrpos($key, 0);
			if ($pos !== false) {
				$array[substr($key, $pos + 1)] = $value;
				unset($array[$key]);
			}
		}
		return $array;
	}

	/**
	 * Are there any attributes not yet retrieved?
	 * 
	 * @return boolean
	 */
	public function isSparse() {
		if (isset(static::$sparse_attributes)) {
			$attr = $this->getAttributes();
			return array_intersect($attr, $this->attr_retrieved) !== $attr;
		}
		return false;
	}

	/**
	 * Get the (non-sparse) attributes and their values of this object.
	 * Relies on getters and setters to update $attr_retrieved
	 * 
	 * @param boolean $primary_key_only
	 * @param boolean $sparse
	 * @return array
	 */
	public function getValues($primary_key_only = false, $sparse = true) {
		$values = array();
		if ($primary_key_only) {
			$attributes = $this->getPrimaryKey();
		} else {
			$attributes = $this->getAttributes();
		}
		// Do not return sparse attribute values that have not been retrieved (these are invalid) unless specifically requested
		if ($sparse AND isset(static::$sparse_attributes)) {
			$attributes = array_intersect($attributes, $this->attr_retrieved);
		}
		foreach ($attributes as $attribute) {
			$values[$attribute] = werkomheen_pdo_bool($this->$attribute);
		}
		if ($primary_key_only) {
			return array_values($values);
		}
		return $values;
	}

	/**
	 * Cast values to defined type.
	 * PDO does not do this automatically (yet).
	 */
	private function castValues() {
		foreach (static::$persistent_attributes as $attribute => $definition) {
			// Only cast retrieved values
			if (isset(static::$sparse_attributes) AND ! in_array($attribute, $this->attr_retrieved)) {
				continue;
			}
			if ($definition[0] === T::Boolean) {
				$this->$attribute = (boolean) $this->$attribute;
			} elseif ($definition[0] === T::Integer) {
				$this->$attribute = (int) $this->$attribute;
			} elseif ($definition[0] === T::Float) {
				$this->$attribute = (float) $this->$attribute;
			} elseif (DB_CHECK AND $definition[0] === T::Enumeration AND ! in_array($this->$attribute, $definition[2]::getTypeOptions())
			) {
				debugprint(static::getTableName() . '.' . $attribute . ' invalid ' . $definition[2] . ' value: ' . $this->$attribute);
			}
		}
	}

}
