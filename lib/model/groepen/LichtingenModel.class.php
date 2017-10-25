<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Lichting;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Persistence\Database;

class LichtingenModel extends AbstractGroepenModel {

	const ORM = Lichting::class;

	public static function get($lidjaar) {
		return static::instance()->nieuw($lidjaar);
	}

	public function nieuw($lidjaar = null) {
		if ($lidjaar === null) {
			$lidjaar = date('Y');
		}
		$lichting = parent::nieuw();
		$lichting->lidjaar = (int)$lidjaar;
		$lichting->id = $lichting->lidjaar;
		$lichting->naam = 'Lichting ' . $lichting->lidjaar;
		$lichting->familie = 'Lichting';
		$lichting->begin_moment = $lichting->lidjaar . '-09-01 00:00:00';
		return $lichting;
	}

	/**
	 * Override normal behaviour.
	 */
	public function find($criteria = null, array $criteria_params = array(), $groupby = null, $orderby = null, $limit = null, $start = 0) {
		$jongste = static::getJongsteLidjaar();
		$oudste = static::getOudsteLidjaar();
		$lichtingen = array();
		for ($lidjaar = $jongste; $lidjaar >= $oudste; $lidjaar--) {
			$lichtingen[] = $this->nieuw($lidjaar);
		}
		return $lichtingen;
	}

	public static function getHuidigeJaargang() {
		$jaar = (int)date('Y');
		$maand = (int)date('m');
		if ($maand < 8) {
			$jaar--;
		}
		return $jaar . '-' . ($jaar + 1);
	}

	public static function getJongsteLidjaar() {
		return (int)Database::instance()->sqlSelect(array('MAX(lidjaar)'), ProfielModel::instance()->getTableName())->fetchColumn();
	}

	public static function getOudsteLidjaar() {
		return (int)Database::instance()->sqlSelect(array('MIN(lidjaar)'), ProfielModel::instance()->getTableName(), 'lidjaar > 0')->fetchColumn();
	}

}
