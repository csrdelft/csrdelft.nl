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
	private $orm_entity;

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
	 * @param int $limit
	 * @param int $start
	 * @return PersistentEntity[]
	 */
	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $limit = null, $start = 0) {
		$select = $this->orm_entity->getFields();
		if ($criteria === null) {
			$criteria = '1';
		}
		$result = Database::sqlSelect($select, $this->orm_entity->getTableName(), $criteria, $criteria_params, $orderby, $limit, $start);
		return $result->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, get_class($this->orm_entity));
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
		$primary_key_values = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$primary_key_values[] = $entity->$key;
		}
		return $this->retrieveByPrimaryKey($primary_key_values);
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
		$result = Database::sqlSelect(array('*'), $this->orm_entity->getTableName(), implode(', ', $where), $primary_key_values, 1);
		return $result->fetchObject(get_class($this->orm_entity));
	}

	/**
	 * Save existing entity.
	 *
	 * @param PersistentEntity $entity
	 */
	public function update(PersistentEntity $entity) {
		$properties = $entity->getValues();
		$where = '';
		$params = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where .= $key . ' = :' . $key; // name parameters after key
			$params[':' . $key] = $properties[$key]; // named parameters
			unset($properties[$key]); // do not update primary key
		}
		$rowcount = Database::sqlUpdate($this->orm_entity->getTableName(), $properties, $where, $params, 1);
		if ($rowcount !== 1) {
			throw new Exception('update rowCount=' . $rowcount);
		}
	}

	/**
	 * Remove existing entity.
	 * 
	 * @param PersistentEntity $entity
	 */
	public function delete(PersistentEntity $entity) {
		$primary_key_values = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$primary_key_values[] = $entity->$key;
		}
		$this->deleteByPrimaryKey($primary_key_values);
	}

	/**
	 * Delete entity by primary key.
	 * 
	 * @param array $primary_key_values
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		$where = array();
		foreach ($this->orm_entity->getPrimaryKey() as $key) {
			$where[] = $key . ' = ?';
		}
		$rowcount = Database::sqlDelete($this->orm_entity->getTableName(), implode(', ', $where), $primary_key_values, 1);
		if ($rowcount !== 1) {
			throw new Exception('delete rowCount=' . $rowcount);
		}
	}

}
