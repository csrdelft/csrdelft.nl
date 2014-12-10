<?php

require_once 'MVC/model/CsrMemcache.singleton.php';

/**
 * CachedPersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Uses runtime cache and Memcache to provide caching on top of PersistenceModel.
 * Lazy loading: request-multiple-retrieve-once entities by primary key from foreign key relations.
 * Prefetch: grouping queries of foreign key relations beforehand.
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
	 * @return string
	 */
	private function cacheKey(array $primary_key_values) {
		return static::orm . crc32(implode('-', $primary_key_values));
	}

	protected function isCached($key, $memcache = false) {
		if (isset($this->runtime_cache[$key])) {
			return true;
		} elseif ($memcache AND CsrMemcache::isAvailable()) {
			// exists without retrieval
			if (CsrMemcache::instance()->add($key, '')) {
				CsrMemcache::instance()->delete($key);
				return false;
			}
			return true;
		}
		return false;
	}

	protected function getCached($key, $memcache = false) {
		if (array_key_exists($key, $this->runtime_cache)) {
			return $this->runtime_cache[$key];
		} elseif ($memcache AND CsrMemcache::isAvailable()) {
			$cache = CsrMemcache::instance()->get($key);
			if ($cache !== false) {
				$value = unserialize($cache);
				// unserialize once 
				$this->setCache($key, $value, false);
				return $value;
			}
		}
	}

	protected function setCache($key, $value, $memcache = false) {
		$this->runtime_cache[$key] = $value;
		if ($memcache AND CsrMemcache::isAvailable()) {
			CsrMemcache::instance()->set($key, serialize($value));
		}
	}

	protected function unsetCache($key, $memcache = false) {
		unset($this->runtime_cache[$key]);
		if ($memcache AND CsrMemcache::isAvailable()) {
			CsrMemcache::instance()->delete($key);
		}
	}

	/**
	 * Remove from memcache rather than flushing.
	 * 
	 * @param boolean $memcache This can be used to partially clear memcache.
	 */
	protected function flushCache($memcache = false) {
		if ($memcache AND CsrMemcache::isAvailable()) {
			// this is obviously not complete at all
			foreach ($this->runtime_cache as $key => $value) {
				CsrMemcache::instance()->delete($key);
			}
		}
		$this->runtime_cache = array();
	}

	/**
	 * Cache entire resultset from a PDOStatement.
	 * Optional: put in memcache.
	 * 
	 * @param PDOStatement|array $resultset
	 * @param boolean $memcache
	 * @return array resultset of PDOStatement
	 */
	protected function cacheResult($resultset, $memcache = false) {
		$cached = array();
		foreach ($resultset as $item) {
			$key = $this->cacheKey($item->getValues(true));
			// do NOT update (requires explicit unsetCache)
			if ($this->isCached($key, $memcache)) {
				$cached[] = $this->getCached($key, $memcache);
			} else {
				$this->setCache($key, $item, $memcache);
				$cached[] = $item;
			}
		}
		return $cached;
	}

	/**
	 * Find and cache existing entities with optional search criteria.
	 * Retrieves all attributes.
	 * Cache prefetch resultset in memcache.
	 * 
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby ORDER BY
	 * @param string $groupby GROUP BY
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return array
	 */
	public function prefetch($criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$key = $this->prefetchKey($criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		if ($this->isCached($key, true)) {
			$result = $this->getCached($key, true);
		} else {
			$result = $this->find($criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		}
		// inladen van memcache in runtime cache
		$cached = $this->cacheResult($result, false);
		if ($result instanceof PDOStatement) {
			$this->setCache($key, $cached, true);
		}
		return $cached;
	}

	/**
	 * Calculate key for caching prefetch resultset.
	 * 
	 * @param array $params
	 * @return string
	 */
	private function prefetchKey($criteria, array $criteria_params, $orderby, $groupby, $limit, $start) {
		$params = array($criteria, implode('+', $criteria_params), $orderby, $groupby, $limit, $start);
		return get_class($this) . crc32(implode('-', $params));
	}

	/**
	 * Find and cache existing entities with optional search criteria.
	 * Retrieves only requested attributes and the primary key values.
	 * 
	 * @param array $attributes to retrieve
	 * @param string $criteria WHERE
	 * @param array $criteria_params optional named parameters
	 * @param string $orderby ORDER BY
	 * @param string $groupby GROUP BY
	 * @param int $limit max amount of results
	 * @param int $start results from index
	 * @return array
	 */
	public function prefetchSparse(array $attributes, $criteria = null, array $criteria_params = array(), $orderby = null, $groupby = null, $limit = null, $start = 0) {
		$result = $this->findSparse($attributes, $criteria, $criteria_params, $orderby, $groupby, $limit, $start);
		return $this->cacheResult($result);
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
	 * Load and cache saved entity data and create new object.
	 * 
	 * @param array $primary_key_values
	 * @return PersistentEntity|false
	 */
	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$key = $this->cacheKey($primary_key_values);
		if ($this->isCached($key)) {
			return $this->getCached($key);
		}
		$result = parent::retrieveByPrimaryKey($primary_key_values);
		if ($result) {
			$this->setCache($key, $result);
		}
		return $result;
	}

	/**
	 * Requires positional values.
	 * 
	 * @param array $primary_key_values
	 * @return boolean rows affected
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		$this->unsetCache($this->cacheKey($primary_key_values), true);
		return parent::deleteByPrimaryKey($primary_key_values);
	}

}
