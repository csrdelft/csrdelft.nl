<?php

exit;

class Child {

	public $naam;
	public $value;
	public $children;

	public function __construct($naam) {
		$this->naam = $naam;
		$this->value = 1;
	}

	public function getValues() {
		return array($this->naam);
	}

}

function createChildren() {
	return array(new Child('A'), new Child('B'), new Child('C'));
}

class Test {

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

	public function getCached($key) {
		return $this->runtime_cache[$key];
	}

	public function setCache($key, $value) {
		$this->runtime_cache[$key] = $value;
	}

	public function unsetCache($key) {
		unset($this->runtime_cache[$key]);
	}

	protected function flushCache() {
		$this->runtime_cache = array();
	}

	public function test() {
		$children = array();
		foreach (createChildren() as $child) {
			$children[] = $child;
			// cache for getParent()
			$key = $this->cacheKey($child->getValues());
			$this->setCache($key, $child);
		}
		return $children;
	}

}

$test = new Test();

$p = new Child('P');
$p->children = $test->test();

$test->setCache(crc32('P'), $p);


var_dump($test->getCached(crc32('P')));


$test->getCached(crc32('A'))->value++;
$test->getCached(crc32('B'))->naam = 'X';

$test->setCache(crc32('B'), new Child('E'));



var_dump($test->getCached(crc32('P')));
