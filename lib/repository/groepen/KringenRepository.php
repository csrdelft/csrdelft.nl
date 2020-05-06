<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Kring;
use CsrDelft\entity\groepen\Verticale;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

class KringenRepository extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Kring::class);
	}

	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null) {
		return parent::findBy($criteria, ['verticale' => 'ASC', 'kring_nummer' => 'ASC'] + ($orderBy ?? []), $limit, $offset);
	}

	public function get($id) {
		if (is_numeric($id)) {
			return parent::get($id);
		}
		list($verticale, $kringNummer) = explode('.', $id);
		return $this->findOneBy(['verticale' => $verticale, 'kring_nummer' => $kringNummer]);
	}

	public function nieuw($letter = null) {
		/** @var Kring $kring */
		$kring = parent::nieuw();
		$kring->verticale = $letter;
		return $kring;
	}
}
