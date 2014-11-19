<?php

/**
 * PersistentAttribute.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Translation of persistent attribute definitions to and from MySQL table structure.
 * 
 */
class PersistentAttribute {

	/**
	 * Name
	 * @var string
	 */
	public $field;
	/**
	 * Type definition
	 * @var int
	 */
	public $type;
	/**
	 * Allowed to be NULL: 'YES' or 'NO'
	 * @var string
	 */
	public $null;
	/**
	 * Key type: 'PRI' or 'MUL' or empty
	 * @var string
	 */
	public $key;
	/**
	 * Default value
	 * @var string
	 */
	public $default;
	/**
	 * Additional properties like 'auto_increment'
	 * @var string
	 */
	public $extra;

	public function toSQL() {
		$sql = $this->field . ' ' . $this->type;
		if ($this->null === 'YES') {
			$sql .= ' NULL';
			if ($this->default === null) {
				$sql .= ' DEFAULT NULL';
			}
		} else {
			$sql .= ' NOT NULL';
			if ($this->default !== null) {
				$sql .= ' DEFAULT "' . $this->default . '"';
			}
		}
		if (!empty($this->extra)) {
			$sql .= ' ' . $this->extra;
		}
		return $sql;
	}

	/**
	 * To compare table description of MySQL.
	 * 
	 * @unsupported keys
	 * 
	 * @param string $name
	 * @param array $definition
	 * @return PersistentAttribute
	 */
	public static function makeAttribute($name, array $definition) {
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
		if ($attribute->type === T::Enumeration) {
			$class = $attribute->extra;
			$attribute->type = 'enum("' . implode('", "', $class::getTypeOptions()) . '")';
			$attribute->extra = '';
		}
		return $attribute;
	}

	/**
	 * To compare table description of MySQL.
	 * 
	 * @unsupported keys
	 * 
	 * @param PersistentAttribute $attribute
	 * @reaturn array $definition
	 */
	public static function makeDefinition(PersistentAttribute $attribute) {
		$definition = array();
		if (startsWith($attribute->type, 'enum')) {
			$start = strpos($attribute->type, '(');
			$length = strpos($attribute->type, ')') - $start;
			$values = explode(',', substr($attribute->type, $start, $length));
			foreach ($values as $i => $value) {
				$values[$i] = str_replace('"', "", $value);
				$values[$i] = str_replace("'", "", $value);
			}
			$definition[] = array(T::Enumeration, false, $values);
		} else {
			if (DB_CHECK AND ! in_array($attribute->type, T::getTypeOptions())) {
				throw new Exception('Unknown persistent attribute type: ' . $attribute->type);
			}
			$definition[] = $attribute->type;
		}
		if ($attribute->null === 'YES') {
			$definition[] = true;
		} else {
			$definition[] = false;
		}
		if (!empty($attribute->extra)) {
			$definition[] = $attribute->extra;
		}
		return $definition;
	}

}
