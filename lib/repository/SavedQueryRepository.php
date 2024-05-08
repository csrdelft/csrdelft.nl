<?php

namespace CsrDelft\repository;

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------

use CsrDelft\entity\SavedQuery;
use CsrDelft\entity\SavedQueryResult;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class SavedQueryRepository
 * @package CsrDelft\repository
 *
 * @method SavedQuery|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavedQuery|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavedQuery[]    findAll()
 * @method SavedQuery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavedQueryRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, SavedQuery::class);
	}

	public function get($id)
	{
		return $this->find($id);
	}

	/**
	 * @return SavedQuery[]
	 */
	public function getQueries()
	{
		return $this->findBy([], ['categorie' => 'ASC']);
	}

	public function loadQuery($queryId): SavedQueryResult
	{
		$query = $this->find($queryId);

		if (!$query || !$query->magBekijken()) {
			throw new AccessDeniedException();
		}

		$resultObject = new SavedQueryResult();
		$resultObject->query = $query;

		try {
			$result = $this->_em
				->getConnection()
				->fetchAllAssociative($query->savedquery);
			$cols = [];

			foreach ($result[0] as $col => $value) {
				$cols[] = $col;
			}

			$resultObject->cols = $cols;
			$resultObject->rows = $result;
		} catch (Exception $ex) {
			$resultObject->cols = [];
			$resultObject->rows = [];
			$resultObject->error = $ex->getMessage();
		}

		return $resultObject;
	}
}
