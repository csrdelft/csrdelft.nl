<?php

namespace CsrDelft\common\cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\InvalidCacheId;
use Memcache;
use function preg_match;
use function strlen;
use function time;

/**
 * Memcache cache provider.
 */
class MemcacheCache extends CacheProvider
{
	public const CACHE_ID_MAX_LENGTH = 250;

	/** @var Memcache|null */
	private $memcache;

	/**
	 * Sets the memcache instance to use.
	 *
	 * @return void
	 */
	public function setMemcache(Memcache $memcache)
	{
		$this->memcache = $memcache;
	}

	/**
	 * Gets the memcache instance used by the cache.
	 *
	 * @return Memcache|null
	 */
	public function getMemcache()
	{
		return $this->memcache;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch($id)
	{
		return $this->memcache->get($this->fixId($id));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains($id)
	{
		return $this->memcache->get($this->fixId($id)) !== false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave($id, $data, $lifeTime = 0)
	{
		$this->validateCacheId($id);

		if ($lifeTime > 30 * 24 * 3600) {
			$lifeTime = time() + $lifeTime;
		}

		return $this->memcache->set($this->fixId($id), $data, null, (int) $lifeTime);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete($id)
	{
		return $this->memcache->delete($this->fixId($id));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFlush()
	{
		return $this->memcache->flush();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doGetStats()
	{
		$stats   = $this->memcache->getStats();

		return [
			Cache::STATS_HITS   => $stats['get_hits'],
			Cache::STATS_MISSES => $stats['get_misses'],
			Cache::STATS_UPTIME => $stats['uptime'],
			Cache::STATS_MEMORY_USAGE     => $stats['bytes'],
			Cache::STATS_MEMORY_AVAILABLE => $stats['limit_maxbytes'],
		];
	}

	private function fixId($id) {
		return urlencode($id);
	}

	/**
	 * Validate the cache id
	 *
	 * @see https://github.com/memcached/memcached/blob/1.5.12/doc/protocol.txt#L41-L49
	 *
	 * @param string $id
	 *
	 * @return void
	 *
	 * @throws InvalidCacheId
	 */
	private function validateCacheId($id)
	{
		if (strlen($id) > self::CACHE_ID_MAX_LENGTH) {
			throw InvalidCacheId::exceedsMaxLength($id, self::CACHE_ID_MAX_LENGTH);
		}

		if (preg_match('/[\t\r\n]/', $id) === 1) {
			throw InvalidCacheId::containsControlCharacter($id);
		}
	}
}
