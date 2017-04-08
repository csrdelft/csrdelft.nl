<?php

class VerticalenModel extends AbstractGroepenModel {

	const ORM = Verticale::class;

	protected static $instance;
	/**
	 * Store verticalen array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'letter ASC';

	public static function get($letter) {
		$verticalen = static::instance()->prefetch('letter = ?', array($letter), null, null, 1);
		return reset($verticalen);
	}

	public function nieuw() {
		$verticale = parent::nieuw();
		$verticale->letter = null;
		return $verticale;
	}

}
