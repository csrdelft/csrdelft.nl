<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Kring;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\security\AccessModel;

class KringenModel extends AbstractGroepenModel {
	public function __construct(AccessModel $accessModel) {
		parent::__static();
		parent::__construct($accessModel);
	}

	const ORM = Kring::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'verticale ASC, kring_nummer ASC';

	public static function get($id) {
		if (is_numeric($id)) {
			return parent::get($id);
		}
		$kringen = static::instance()->prefetch('verticale = ? AND kring_nummer = ?', explode('.', $id), null, null, 1);
		return reset($kringen);
	}

	public function nieuw($letter = null) {
		/** @var Kring $kring */
		$kring = parent::nieuw();
		$kring->verticale = $letter;
		return $kring;
	}

	public function getKringenVoorVerticale(Verticale $verticale) {
		return $this->prefetch('verticale = ?', [$verticale->letter]);
	}

}
