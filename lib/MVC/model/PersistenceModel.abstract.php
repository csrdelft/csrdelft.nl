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
 * 
 */
abstract class PersistenceModel implements Persistence {

	/**
	 * ORM entity class
	 * @var PersistentEntity
	 */
	protected $orm_entity;

	/**
	 * Requires an entity class for ORM
	 * @param PersistentEntity $orm_entity
	 */
	protected function __construct(PersistentEntity $orm_entity) {
		$this->orm_entity = $orm_entity;
	}

	/**
	 * Find existing entities.
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
		return $result->fetchAll(PDO::FETCH_CLASS, get_class($this->orm_entity));
	}

	/**
	 * Check if entities exist.
	 * 
	 * @param string $criteria
	 * @param array $criteria_params
	 * @return boolean
	 */
	public function exists($criteria = null, array $criteria_params = array()) {
		return Database::sqlExists($this->orm_entity->getTableName(), $criteria, $criteria_params);
	}

	/**
	 * Check if entity exists by primary key.
	 * 
	 * @param array $primary_key_values
	 * @return boolean
	 */
	protected function existsByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		return $this->exists($where, $primary_key_values);
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
	 * Load entity data.
	 * 
	 * @param PersistentEntity $entity
	 * @return PersistentEntity
	 */
	public function retrieve(PersistentEntity $entity) {
		return $this->retrieveByPrimaryKey(array_values($entity->getValues(true)));
	}

	/**
	 * Get entity by primary key.
	 * 
	 * @param array $primary_key_values
	 * @return PersistentEntity
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$result = Database::sqlSelect($this->orm_entity->getFields(), $this->orm_entity->getTableName(), implode(' AND ', $where), $primary_key_values, 1);
		return $result->fetchObject(get_class($this->orm_entity));
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
		return $this->deleteByPrimaryKey(array_values($entity->getValues(true)));
	}

	/**
	 * Delete entity by primary key.
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
