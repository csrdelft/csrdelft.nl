<?php

require_once 'MVC/model/framework/PersistenceModel.abstract.php';
require_once 'MVC/model/framework/DatabaseAdmin.singleton.php';
require_once 'MVC/model/entity/framework/DynamicEntityDefinition.class.php';
require_once 'MVC/model/entity/framework/DynamicEntity.class.php';

/**
 * DynamicEntityModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Defines the DynamicEntity class by the DynamicEntityDefinition.
 * Factory pattern instead of singleton, so ::instance() won't work!
 * 
 */
class DynamicEntityModel extends PersistenceModel {

	const orm = 'DynamicEntity';

	protected static $instance;

	public static function makeModel($table_name) {
		return new DynamicEntityModel($table_name);
	}

	/**
	 * Definition of the DynamicEntity
	 * @var DynamicEntityDefinition
	 */
	protected $definition;

	/**
	 * Override the constructor of PersistentModel and create DynamicEntityDefinition from table structure.
	 * 
	 * @param string $table_name
	 */
	protected function __construct($table_name) {
		$this->definition = new DynamicEntityDefinition();
		$this->definition->table_name = $table_name;
		foreach (DatabaseAdmin::instance()->sqlDescribeTable($this->definition->table_name) as $attribute) {
			$this->definition->persistent_attributes[] = PersistentAttribute::makeDefinition($attribute);
			if ($attribute->key === 'PRI') {
				$this->definition->primary_key[] = $attribute->field;
			}
		}
		$this->orm = new DynamicEntity(false, null, $this->definition);
	}

	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$entity = parent::retrieveByPrimaryKey($primary_key_values);
		$entity->definition = $this->definition;
		return $entity;
	}

	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$result = parent::find($criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		$result->setFetchMode(PDO::FETCH_CLASS, static::orm, array($cast = true, null, $this->definition));
		return $result;
	}

	public function findSparse(array $attributes, $criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$result = parent::findSparse($attributes, $criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		$result->setFetchMode(PDO::FETCH_CLASS, self::orm, array($cast = true, $attributes, $this->definition));
		return $result;
	}

}
