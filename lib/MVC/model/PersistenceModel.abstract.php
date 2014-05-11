<?php

require_once 'MVC/model/DatabaseAdmin.singleton.php';
require_once 'MVC/model/Persistence.interface.php';
require_once 'MVC/model/entity/PersistentEntity.abstract.php';

/**
 * PersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Uses database to provide persistence.
 * Requires a static property $orm in superclass.
 * Requires a static property $instance in superclass.
 * 
 */
abstract class PersistenceModel implements Persistence {

	public static function instance() {
		if (!isset(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	protected function __construct($subdir = '') {
		$orm = static::orm;
		require_once 'MVC/model/entity/' . $subdir . $orm . '.class.php';
		$orm::__constructStatic();
		if (defined('DB_CHECK')) {
			$orm::checkTable();
		}
	}

	/**
	 * Find existing entities with optional search criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PersistentEntity[]
	 */
	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $limit = null, $start = 0) {
		$orm = static::orm;
		$result = Database::sqlSelect($orm::getFields(), $orm::getTableName(), $criteria, $criteria_params, $orderby, $limit, $start);
		$result->setFetchMode(PDO::FETCH_CLASS, $orm);
		return $result;
	}

	/**
	 * Count existing entities with optional criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @return int count
	 */
	public function count($criteria = null, array $criteria_params = array()) {
		$orm = static::orm;
		$result = Database::sqlSelect(array('COUNT(*)'), $orm::getTableName(), $criteria, $criteria_params);
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
		$orm = static::orm;
		return Database::sqlExists($orm::getTableName(), $criteria, $criteria_params);
	}

	/**
	 * Check if enitity exists.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function exists(PersistentEntity $entity) {
		return $this->existsByPrimaryKeys($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_keys_values
	 * @return boolean primary key exists
	 */
	protected function existsByPrimaryKeys(array $primary_keys_values) {
		$orm = static::orm;
		$where = array();
		foreach ($orm::getPrimaryKeys() as $key) {
			$where[] = $key . ' = ?';
		}
		return $this->exist(implode(' AND ', $where), $primary_keys_values);
	}

	/**
	 * Save new entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function create(PersistentEntity $entity) {
		return Database::sqlInsert($entity::getTableName(), $entity->getValues());
	}

	/**
	 * Load saved enitity data and replace entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return PersistentEntity
	 */
	public function retrieve(PersistentEntity $entity) {
		$entity = $this->retrieveByPrimaryKeys($entity->getValues(true));
		return $entity;
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_keys_values
	 * @return PersistentEntity or FALSE on failure
	 */
	protected function retrieveByPrimaryKeys(array $primary_keys_values) {
		$orm = static::orm;
		$where = array();
		foreach ($orm::getPrimaryKeys() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect($orm::getFields(), $orm::getTableName(), implode(' AND ', $where), $primary_keys_values, null, 1);
		return $result->fetchObject($orm);
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
		foreach ($entity::getPrimaryKeys() as $key) {
			$where[] = $key . ' = :W' . $key; // name parameters after column
			$params[':W' . $key] = $properties[$key];
			unset($properties[$key]); // do not update primary key
		}
		return Database::sqlUpdate($entity::getTableName(), $properties, implode(' AND ', $where), $params, 1);
	}

	/**
	 * Remove existing entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return boolean rows affected === 1
	 */
	public function delete(PersistentEntity $entity) {
		return $this->deleteByPrimaryKeys($entity->getValues(true));
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_keys_values
	 * @return boolean rows affected === 1
	 */
	protected function deleteByPrimaryKeys(array $primary_keys_values) {
		$orm = static::orm;
		$where = array();
		foreach ($orm::getPrimaryKeys() as $key) {
			$where[] = $key . ' = ?';
		}
		return 1 === Database::sqlDelete($orm::getTableName(), implode(' AND ', $where), $primary_keys_values, 1);
	}

}
