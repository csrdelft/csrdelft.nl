<?php

namespace CsrDelft\repository\documenten;

use CsrDelft\entity\documenten\Document;
use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\model\OrmTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDOStatement;

/**
 * Class DocumentCategorieModel.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @method DocumentCategorie[]    ormFind($criteria = null, $criteria_params = [], $group_by = null, $order_by = null, $limit = null, $start = 0)
 * @method DocumentCategorie|null doctrineFind($id, $lockMode = null, $lockVersion = null)
 * @method DocumentCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentCategorie[]    findAll()
 * @method DocumentCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentCategorieRepository extends ServiceEntityRepository {
	use OrmTrait;

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

	/**
	 * @param DocumentCategorie $categorie
	 * @param $aantal
	 *
	 * @return PDOStatement|Document[]
	 */
	public function getRecent(DocumentCategorie $categorie, $aantal) {
		return $this->documentRepository->ormFind(
			'categorie_id = ?',
			[$categorie->id],
			null,
			'toegevoegd DESC',
			$aantal
		);
	}
}
