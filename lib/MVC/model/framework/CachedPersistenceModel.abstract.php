<?php

require_once 'MVC/model/framework/PersistenceModel.abstract.php';

/**
 * CachedPersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Uses runtime cache to provide caching on top of PersistenceModel.
 * Very useful for (lazy loading of) retrieved entities from foreign key relations.
 * Prefetch is useful for grouping queries of foreign key relations.
 * 
 * N.B. modifying objects in the cache affects every reference to it!
 * 
 */
abstract class CachedPersistenceModel extends PersistenceModel {

	private $runtime_cache = array();

	/**
	 * Calculate key for caching.
	 * 
	 * @param array $primary_key_values
	 * @return int
	 */
	protected function cacheKey(array $primary_key_values) {
		return crc32(implode('', $primary_key_values));
	}

	protected function isCached($key) {
		return isset($this->runtime_cache[$key]);
	}

	protected function getCached($key) {
		return $this->runtime_cache[$key];
	}

	protected function setCache($key, $value) {
		$this->runtime_cache[$key] = $value;
	}

	protected function unsetCache($key) {
		unset($this->runtime_cache[$key]);
	}

	protected function flushCache() {
		$this->runtime_cache = array();
	}

	/**
	 * Find and cache existing entities with optional search criteria.
	 * Retrieves all attributes.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PDOStatement
	 */
	public function prefetch($criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$result = parent::find($criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		foreach ($result as $item) {
			$this->setCache($this->cacheKey($item->getValues(true)));
		}
	}

	/**
	 * Find and cache existing entities with optional search criteria.
	 * Retrieves only requested attributes.
	 * 
	 * @param array $attributes to retrieve
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return PDOStatement
	 */
	public function prefetchSparse(array $attributes, $criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$result = parent::findSparse($attributes, $criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		foreach ($result as $item) {
			$this->setCache($this->cacheKey($item->getValues(true)));
		}
	}

	/**
	 * Check if enitity with primary key exists.
	 * 
	 * @param array $primary_key_values
	 * @return boolean primary key exists
	 */
	protected function existsByPrimaryKey(array $primary_key_values) {
		$key = $this->cacheKey($primary_key_values);
		if ($this->isCached($key)) {
			return true;
		} else {
			return parent::existsByPrimaryKey($primary_key_values);
		}
	}

	/**
	 * Load saved entity data and create new object.
	 * 
	 * @param array $primary_key_values
	 * @return PersistentEntity|false
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$key = $this->cacheKey($primary_key_values);
		if ($this->isCached($key)) {
			return $this->getCached($key);
		} else {
			return parent::retrieveByPrimaryKey($primary_key_values);
		}
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return boolean rows affected
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		$this->unsetCache($this->cacheKey($primary_key_values));
		return parent::deleteByPrimaryKey($primary_key_values);
	}

}
