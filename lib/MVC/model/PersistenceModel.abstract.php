<?php

require_once 'MVC/model/Database.singleton.php';
require_once 'MVC/model/Persistence.interface.php';
require_once 'MVC/model/entity/PersistentEntity.abstract.php';

/**
 * PersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Uses database to provide persistence.
 * Requires a static property $instance in superclass.
 * Requires an ORM class constant to be defined in superclass.
 */
abstract class PersistenceModel implements Persistence {

	public static function instance() {
		if (!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * ORM entity class
	 * @var PersistentEntity
	 */
	private $orm_entity;

	protected function __construct($subdir = '') {
		$class = static::orm;
		require_once 'MVC/model/entity/' . $subdir . $class . '.class.php';
		$this->orm_entity = new $class();
	}

	/**
	 * Find existing entities with optional search criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby
	 * @param int $limit max amount of results
	 * @param int $start resultset from index
	 * @return PersistentEntity[]
	 */
	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $limit = null, $start = 0) {
		$result = Database::sqlSelect($this->orm_entity->getFields(), $this->orm_entity->getTableName(), $criteria, $criteria_params, $orderby, $limit, $start);
		return $result->fetchAll(PDO::FETCH_CLASS, static::orm);
	}

	/**
	 * Count existing entities with optional criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @return int count
	 */
	public function count($criteria = null, array $criteria_params = array()) {
		$result = Database::sqlSelect(array('COUNT(*)'), $this->orm_entity->getTableName(), $criteria, $criteria_params);
		return (int) $result->fetchColumn();
	}

	/**
	 * Check if entities with optional search criteria exist.
	 * 
	 * @param string $criteria
	 * @param array $criteria_params
	 * @return boolean entities with search criteria exist
	 */
	public function exist($criteria = null, array $criteria_params = array()) {
		return Database::sqlExists($this->orm_entity->getTableName(), $criteria, $criteria_params);
	}

	/**
	 * Check if enitity exists.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function exists(PersistentEntity $entity) {
		return $this->existsByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return boolean primary key exists
	 */
	protected function existsByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return $this->exist(implode(' AND ', $where), $primary_key_values);
	}

	/**
	 * Save new entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function create(PersistentEntity $entity) {
		return Database::sqlInsert($this->orm_entity->getTableName(), $entity->getValues());
	}

	/**
	 * Load saved enitity data and replace entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return PersistentEntity
	 */
	public function retrieve(PersistentEntity $entity) {
		$entity = $this->retrieveByPrimaryKey($entity->getValues(true));
		return $entity;
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return PersistentEntity or false on failure
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect($this->orm_entity->getFields(), $this->orm_entity->getTableName(), implode(' AND ', $where), $primary_key_values, null, 1);
		return $result->fetchObject(static::orm);
	}

	/**
	 * Save existing entity.
	 *
	 * @param PersistentEntity $entity
	 * @return int rows affected
	 */
	public function update(PersistentEntity $entity) {
		$properties = $entity->getValues();
		$where = array();
		$params = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = :' . $key; // name parameters after column
			$params[':' . $key] = $properties[$key];
			unset($properties[$key]); // do not update primary key
		}
		return Database::sqlUpdate($this->orm_entity->getTableName(), $properties, implode(' AND ', $where), $params, 1);
	}

	/**
	 * Remove existing entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return boolean rows affected === 1
	 */
	public function delete(PersistentEntity $entity) {
		return $this->deleteByPrimaryKey($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return boolean rows affected === 1
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return 1 === Database::sqlDelete($this->orm_entity->getTableName(), implode(' AND ', $where), $primary_key_values, 1);
	}

}
