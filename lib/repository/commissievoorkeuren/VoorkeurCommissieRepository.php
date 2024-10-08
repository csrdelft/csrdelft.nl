<?php

namespace CsrDelft\repository\commissievoorkeuren;

use CsrDelft\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class VoorkeurCommissieRepository
 * @package CsrDelft\repository\commissievoorkeuren
 * @method VoorkeurCommissie|null find($id, $lockMode = null, $lockVersion = null)
 * @method VoorkeurCommissie|null findOneBy(array $criteria, array $orderBy = null)
 * @method VoorkeurCommissie[]    findAll()
 * @method VoorkeurCommissie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VoorkeurCommissieRepository extends AbstractRepository
{
	public function __construct(
		private readonly VoorkeurCommissieCategorieRepository $voorkeurCommissieCategorieModel,
		ManagerRegistry $registry
	) {
		parent::__construct($registry, VoorkeurCommissie::class);
	}

	public function getByCategorie()
	{
		$categorien = $this->voorkeurCommissieCategorieModel->findAll();
		$cat2commissie = [];
		foreach ($categorien as $cat) {
			$cat2commissie[$cat->id] = ['categorie' => $cat, 'commissies' => []];
		}

		$commissies = $this->findBy([], ['naam' => 'DESC']);

		foreach ($commissies as $commissie) {
			$cat2commissie[$commissie->categorie_id]['commissies'][] = $commissie;
		}
		return $cat2commissie;
	}
}
