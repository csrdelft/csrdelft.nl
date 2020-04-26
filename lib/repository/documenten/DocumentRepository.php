<?php

namespace CsrDelft\repository\documenten;

use CsrDelft\entity\documenten\Document;
use CsrDelft\model\OrmTrait;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use PDOStatement;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @method Document[]    ormFind($criteria = null, $criteria_params = [], $group_by = null, $order_by = null, $limit = null, $start = 0)
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends AbstractRepository {
	use OrmTrait;

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Document::class);
	}

	/**
	 * @param $id
	 *
	 * @return Document|false
	 */
	public function get($id) {
		return $this->find($id);
	}

	/**
	 * @param $zoekterm
	 * @param int $limiet
	 *
	 * @return PDOStatement|Document[]
	 */
	public function zoek($zoekterm, $limiet = 0) {

		return $this->ormFind(
			'MATCH (naam, filename) AGAINST (? IN NATURAL LANGUAGE MODE)',
			[$zoekterm],
			null,
			null,
			$limiet
		);

	}
}
