<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\security\AccessModel;

class VerticalenModel extends AbstractGroepenModel {
	public function __construct(AccessModel $accessModel) {
		parent::__static();
		parent::__construct($accessModel);
	}

	const ORM = Verticale::class;

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
		$verticale = reset($verticalen);
		if (!empty($verticale)) {
			return $verticale;
		}

		return parent::get($letter);
	}

	public function nieuw($soort = null) {
		/** @var Verticale $verticale */
		$verticale = parent::nieuw();
		$verticale->letter = null;
		return $verticale;
	}

}
