<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Ondervereniging;
use CsrDelft\model\entity\groepen\OnderverenigingStatus;
use CsrDelft\model\security\LoginModel;

class OnderverenigingenModel extends AbstractGroepenModel {
	public function __construct() {
		parent::__static();
		parent::__construct();
	}

	const ORM = Ondervereniging::class;

	public function nieuw($soort = null) {
		/** @var Ondervereniging $ondervereniging */
		$ondervereniging = parent::nieuw();
		$ondervereniging->status = OnderverenigingStatus::AdspirantOndervereniging;
		$ondervereniging->status_historie = '[div]Aangemaakt als ' . OnderverenigingStatus::getDescription($ondervereniging->status) . ' door [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $ondervereniging;
	}

}
