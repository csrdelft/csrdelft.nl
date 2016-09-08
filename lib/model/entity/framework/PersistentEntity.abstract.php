<?php

require_once 'model/entity/framework/Sparse.interface.php';
require_once 'model/entity/framework/PersistentEnum.interface.php';
require_once 'model/entity/framework/PersistentAttributeType.enum.php';
require_once 'model/entity/framework/PersistentAttribute.class.php';

/**
 * PersistentEntity.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Requires static properties in subclass: $persistent_attributes, $primary_key and $table_name
 * 
 * @see PersistenceModel->retrieveAttributes for a usage example of sparse and foreign keys.
 * 
 * Optional: static $rename_attributes = array('oldname' => 'newname');
 */
abstract class PersistentEntity implements Sparse, JsonSerializable {

	/**
	 * Static constructor is called (by inheritance) once and only from PersistenceModel.
	 * 
	 * Optional: run conversion code before checkTables() here
	 */
	public static function __static() {
		/*
		 * Override this to extend the persistent attributes:
		 * 
		  parent::__static();
		  self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
		 * 
		 */
	}

	/**
	 * The names of attributes that have been retrieved for this instance.
	 * Relies on getters and setters to update this in case of sparse retrieval.
	 * Used to discern unset values as these are invalid.
	 * @var array
	 */
	protected $attributes_retrieved;

	/**
	 * Constructor is called late (after attributes are set)
	 * by PDO::FETCH_CLASS with $cast = true
	 * 
	 * @param boolean $cast Regular construction should not cast (unset) attributes!
	 * @param array $attributes_retrieved Names of attributes that are set before construction in case of sparse retrieval
	 */
	public function __construct($cast = false, array $attributes_retrieved = null) {
		if ($attributes_retrieved !== null) {
			// Bookkeeping only if not all attributes are set before construction
			$this->attributes_retrieved = $attributes_retrieved;
		} else {
			// Cast all attributes
			$attributes_retrieved = $this->getAttributes();
		}
		if ($cast) {
			$this->castValues($attributes_retrieved);
		}
	}

	public function getTableName() {
		return static::$table_name;
	}

	/**
	 * Get all attribute names.
	 * 
	 * @return array
	 */
	public function getAttributes() {
		return array_keys(static::$persistent_attributes);
	}

	public function getAttributeDefinition($attribute_name) {
		return static::$persistent_attributes[$attribute_name];
	}

	public function getPrimaryKey() {
		return array_values(static::$primary_key);
	}

	public function getUUID() {
		return strtolower(implode('.', $this->getValues(true)) . '@' . get_class($this) . '.csrdelft.nl');
	}

	public function jsonSerialize() {
		$array = get_object_vars($this);
		$array['UUID'] = $this->getUUID();
		return $array;
	}

	/**
	 * Are there any attributes not yet retrieved?
	 * Requires tracking of retrieved attributes to discern invalid values.
	 * 
	 * @param array $attributes to check for
	 * @return boolean
	 */
	public function isSparse(array $attributes = null) {
		if (isset($this->attributes_retrieved)) {
			if (empty($attributes)) {
				$attributes = $this->getAttributes();
			}
			return array_intersect($attributes, $this->attributes_retrieved) !== $attributes;
		}
		return false;
	}

	public function onAttributesRetrieved(array $attributes) {
		$this->attributes_retrieved = array_merge($this->attributes_retrieved, $attributes); // Bookkeeping
		$this->castValues($attributes); // PDO does not cast values automatically (yet)
	}

	/**
	 * Get the (non-sparse) attributes and their values of this object.
	 * Relies on getters and setters to update $attributes_retrieved
	 * 
	 * @param boolean $primary_key_only
	 * @return array
	 */
	public function getValues($primary_key_only = false) {
		$values = array();
		if ($primary_key_only) {
			$attributes = $this->getPrimaryKey();
		} else {
			$attributes = $this->getAttributes();
		}
		// Do not return sparse attribute values as these are invalid
		if (isset($this->attributes_retrieved)) {
			$attributes = array_intersect($attributes, $this->attributes_retrieved);
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
	 * PDO does not cast values automatically (yet).
	 * 
	 * @param boolean $attributes Attributes to cast
	 */
	private function castValues(array $attributes) {
		foreach ($attributes as $attribute) {
			$definition = $this->getAttributeDefinition($attribute);
			if (isset($definition[1]) AND $definition[1] AND $this->$attribute === null) {
				// Do not cast allowed null fields
			} elseif ($definition[0] === T::Boolean) {
				$this->$attribute = (boolean) $this->$attribute;
			} elseif ($definition[0] === T::Integer) {
				$this->$attribute = (int) $this->$attribute;
			} elseif ($definition[0] === T::Float) {
				$this->$attribute = (float) $this->$attribute;
			} else {
				$this->$attribute = (string) $this->$attribute;
			}
			if (DB_CHECK AND $definition[0] === T::Enumeration AND ! in_array($this->$attribute, $definition[2]::getTypeOptions())) {
				$msg = static::$table_name . '.' . $attribute . ' invalid ' . $definition[2] . '.enum value: "' . $this->$attribute . '"';
				debugprint($msg);
			}
		}
	}

	/**
	 * Check for differences in persistent attributes.
	 * 
	 * @unsupported INDEX check; FOREIGN KEY check;
	 */
	public static function checkTable() {
		$class = get_called_class();
		$attributes = array();
		foreach (static::$persistent_attributes as $name => $definition) {
			$attributes[$name] = PersistentAttribute::makeAttribute($name, $definition);
			if (in_array($name, static::$primary_key)) {
				$attributes[$name]->key = 'PRI';
			} else {
				$attributes[$name]->key = '';
			}
		}
		try {
			$database_attributes = group_by_distinct('field', DatabaseAdmin::instance()->sqlDescribeTable(static::$table_name));
		} catch (Exception $e) {
			if (endsWith($e->getMessage(), static::$table_name . "' doesn't exist")) {
				DatabaseAdmin::instance()->sqlCreateTable(static::$table_name, $attributes, static::$primary_key);
				return;
			} else {
				throw $e; // Rethrow to controller
			}
		}
		// Rename attributes
		if (property_exists($class, 'rename_attributes')) {
			$rename = static::$rename_attributes;
			foreach ($rename as $oldname => $newname) {
				if (property_exists($class, $newname)) {
					DatabaseAdmin::instance()->sqlChangeAttribute(static::$table_name, $attributes[$newname], $oldname);
				}
			}
		} else {
			$rename = array();
		}
		$previous_attribute = null;
		foreach (static::$persistent_attributes as $name => $definition) {
			// Add missing persistent attributes
			if (!isset($database_attributes[$name])) {
				if (!isset($rename[$name])) {
					DatabaseAdmin::instance()->sqlAddAttribute(static::$table_name, $attributes[$name], $previous_attribute);
				}
			} else {
				// Check exisiting persistent attributes for differences
				$diff = false;
				if ($attributes[$name]->type !== $database_attributes[$name]->type) {
					if ($definition[0] === T::Enumeration) {
						$enum = $definition[2];
						if ($database_attributes[$name]->type !== "enum('" . implode("','", $enum::getTypeOptions()) . "')") {
							$diff = true;
						}
					} else {
						$diff = true;
					}
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
				// TODO: support other key types: MUL, UNI, etc.
				if ($attributes[$name]->key !== $database_attributes[$name]->key AND ( $attributes[$name]->key === 'PRI' OR $database_attributes[$name]->key === 'PRI' )) {
					$diff = true;
				}
				if ($diff) {
					DatabaseAdmin::instance()->sqlChangeAttribute(static::$table_name, $attributes[$name]);
				}
			}
			$previous_attribute = $name;
		}
		// Remove non-persistent attributes
		foreach ($database_attributes as $name => $attribute) {
			if (!isset(static::$persistent_attributes[$name]) AND ! isset($rename[$name])) {
				DatabaseAdmin::instance()->sqlDeleteAttribute(static::$table_name, $attribute);
			}
		}
	}

}
