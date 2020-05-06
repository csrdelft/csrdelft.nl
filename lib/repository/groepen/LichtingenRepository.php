<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\Lichting;
use CsrDelft\model\security\AccessModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class LichtingenRepository extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Lichting::class);
	}

	public function get($lidjaar) {
		return $this->nieuw($lidjaar);
	}

	public function nieuw($lidjaar = null) {
		if ($lidjaar === null) {
			$lidjaar = date('Y');
		}
		/** @var Lichting $lichting */
		$lichting = parent::nieuw();
		$lichting->lidjaar = (int)$lidjaar;
		$lichting->id = $lichting->lidjaar;
		$lichting->naam = 'Lichting ' . $lichting->lidjaar;
		$lichting->familie = 'Lichting';
		$lichting->begin_moment = $lichting->lidjaar . '-09-01 00:00:00';
		return $lichting;
	}

	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
		$jongste = static::getJongsteLidjaar();
		$oudste = static::getOudsteLidjaar();
		$lichtingen = [];
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
		return (int)ContainerFacade::getContainer()->get(Database::class)->sqlSelect(['MAX(lidjaar)'], 'profielen')->fetchColumn();
	}

	public static function getOudsteLidjaar() {
		return (int)ContainerFacade::getContainer()->get(Database::class)->sqlSelect(['MIN(lidjaar)'], 'profielen', 'lidjaar > 0')->fetchColumn();
	}

}
