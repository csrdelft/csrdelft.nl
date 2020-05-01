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

	const ORM = Kring::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'verticale ASC, kring_nummer ASC';

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
