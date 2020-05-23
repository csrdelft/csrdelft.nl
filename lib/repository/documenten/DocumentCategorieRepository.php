<?php

namespace CsrDelft\repository\documenten;

use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class DocumentCategorieModel.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @method DocumentCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentCategorie[]    findAll()
 * @method DocumentCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentCategorieRepository extends AbstractRepository {
	/**
	 * @var DocumentRepository
	 */
	private $documentRepository;

	public function __construct(ManagerRegistry $registry, DocumentRepository $documentRepository) {
		parent::__construct($registry, DocumentCategorie::class);
		$this->documentRepository = $documentRepository;
	}

	/**
	 * @param $id
	 *
	 * @return DocumentCategorie|null
	 */
	public function get($id) {
		return $this->find($id);
	}

	/**
	 * @return array
	 */
	public function getCategorieNamen() {
		$categorien = $this->findAll();

		$return = [];

		foreach ($categorien as $categorie) {
			$return[$categorie->id] = $categorie->naam;
		}

		return $return;
	}
}
