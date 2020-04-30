<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Verticale;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class VerticalenModel extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Verticale::class);
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

	public function get($letter) {
		$verticalen = $this->prefetch('letter = ?', array($letter), null, null, 1);
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
