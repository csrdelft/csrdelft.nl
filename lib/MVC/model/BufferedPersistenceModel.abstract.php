<?php

require_once 'MVC/model/CsrMemcache.singleton.php';
require_once 'MVC/model/PersistenceModel.abstract.php';

/**
 * BufferedPersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Uses Memcache to provide buffering on top of PersistenceModel.
 * 
 * Requires an ORM class constant to be defined in superclass. 
 * Requires a static property $instance in superclass.
 * 
 */
abstract class BufferedPersistenceModel extends PersistenceModel {

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
	public function find($criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		return parent::find($criteria, $criteria_params, $orderby, $groupby, $limit, $start);
	}

	/**
	 * Count existing entities with optional criteria.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @return int count
	 */
	public function count($criteria = null, array $criteria_params = array()) {
		return parent::count($criteria, $criteria_params);
	}

	/**
	 * Check if entities with optional search criteria exist.
	 * 
	 * @param string $criteria
	 * @param array $criteria_params
	 * @return boolean entities with search criteria exist
	 */
	public function exist($criteria = null, array $criteria_params = array()) {
		return parent::exist($criteria, $criteria_params);
	}

	/**
	 * Save new entity.
	 * 
	 * @param PersistentEntity $entity
	 * @return string last insert id
	 */
	public function create(PersistentEntity $entity) {
		return parent::create($entity);
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return PersistentEntity or FALSE on failure
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		return parent::retrieveByPrimaryKey($primary_key_values);
	}

	/**
	 * Save existing entity.
	 *
	 * @param PersistentEntity $entity
	 * @return int rows affected
	 */
	public function update(PersistentEntity $entity) {
		return parent::update($entity);
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return boolean rows affected === 1
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		return parent::deleteByPrimaryKey($primary_key_values);
	}

}
