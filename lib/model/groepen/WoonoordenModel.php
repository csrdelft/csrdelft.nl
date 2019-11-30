<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\HuisStatus;
use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\security\LoginModel;

class WoonoordenModel extends AbstractGroepenModel {
	public function __construct() {
		parent::__static();
		parent::__construct();
	}

	const ORM = Woonoord::class;

	public function nieuw($soort = null) {
		/** @var Woonoord $woonoord */
		$woonoord = parent::nieuw();
		$woonoord->status = HuisStatus::Woonoord;
		$woonoord->status_historie = '[div]Aangemaakt als ' . HuisStatus::getDescription($woonoord->status) . ' door [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $woonoord;
	}
}
