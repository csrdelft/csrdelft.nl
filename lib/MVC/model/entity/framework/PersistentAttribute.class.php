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
		return $this->field . ' ' . $this->type .
				($this->null === 'YES' ? '' : ' NOT NULL') .
				($this->default === null ? '' : ' DEFAULT "' . $this->default . '"') .
				(empty($this->extra) ? '' : ' ' . $this->extra);
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
		if ($attribute->type === T::Boolean) {
			$attribute->type = 'tinyint(1)';
		} elseif ($attribute->type === T::Integer) {
			$attribute->type = 'int(11)';
		} elseif ($attribute->type === T::DateTime) {
			$attribute->type = 'datetime';
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
		if ($attribute->type === 'tinyint(1)') {
			$definition[] = T::Boolean;
		} elseif ($attribute->type === 'int(11)') {
			$definition[] = T::Integer;
		} elseif ($attribute->type === 'datetime' OR $attribute->type === 'timestamp') {
			$definition[] = T::DateTime;
		} elseif ($attribute->type === 'varchar(4)') {
			$definition[] = T::UID;
		} elseif ($attribute->type === 'char(1)') {
			$definition[] = T::Char;
		} elseif (startsWith($attribute->type, 'enum')) {
			$start = strpos($attribute->type, '(');
			$length = strpos($attribute->type, ')') - $start;
			$values = explode(',', substr($attribute->type, $start, $length));
			foreach ($values as $i => $value) {
				$values[$i] = str_replace('"', "", $value);
				$values[$i] = str_replace("'", "", $value);
			}
			$definition[] = array(T::Enumeration, false, $values);
		} else {
			$definition[] = T::String;
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
