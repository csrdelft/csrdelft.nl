<?php

require_once 'MVC/model/PersistenceModel.abstract.php';
require_once 'MVC/model/CsrMemcache.singleton.php';

/**
 * CachedPersistenceModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Uses Memcache to provide caching on top of PersistenceModel.
 * 
 */
abstract class CachedPersistenceModel implements Persistence {

	private $persistence;
	private $cache;

	public function __construct(PersistenceModel $model) {
		$this->persistence = $model;
		$this->cache = CsrMemcache::instance();
	}

	public function create(PersistentEntity $entity) {
		return $this->persistence->create($entity); // unable to cache because last insert id may be used in UUID
	}

	public function retrieve(PersistentEntity $entity) {
		$key = crc32($entity->getUUID());
		$cache = $this->cache->get($key);
		if ($cache) {
			return unserialize($cache);
		}
		$value = $this->persistence->retrieve($entity);
		$this->cache->set($key, serialize($value));
		return $value;
	}

	public function update(PersistentEntity $entity) {
		$rowcount = $this->persistence->update($entity);
		if ($rowcount > 0) {
			$key = crc32($entity->getUUID());
			$value = serialize($entity);
			$cache = $this->cache->replace($key, $value);
			if (!$cache) {
				$this->cache->set($key, $value);
			}
		}
		return $rowcount;
	}

	public function delete(PersistentEntity $entity) {
		$rowcount = $this->persistence->delete($entity);
		if ($rowcount > 0) {
			$this->flush($entity);
		}
		return $rowcount;
	}

	public function flush(PersistentEntity $entity) {
		$key = crc32($entity->getUUID());
		$this->cache->delete($key);
	}

}
