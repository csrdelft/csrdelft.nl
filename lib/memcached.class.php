<?php
/*
 * class.memcached.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Simpele wrapper voor memchached
 */
class Memcached{
	private static $instance;

	private $memcache;
	private $connected=false;

	public static function instance(){
		if(!isset(self::$instance)){
			self::$instance=new Memcached();
		}
		return self::$instance;
	}
	private function __construct(){
		if(class_exists('Memcache')){
			$this->memcache=new Memcache;
			$this->connected=@$this->memcache->connect('unix://'.DATA_PATH.'/csrdelft-cache.socket', 0);

		}
	}

	public function set($key, $value){
		if($this->connected){
			$this->memcache->set($key, $value);
		}
	}

	public function get($key){
		if($this->connected){
			return $this->memcache->get($key);
		}
		return false;
	}
	public function delete($key){
		if($this->connected){
			return $this->memcache->delete($key);
		}
	}
	public function getStats(){
		return $this->memcache->getStats();
	}
	public function flush(){
		return $this->memcache->flush();
	}
}

?>
