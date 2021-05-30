<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\common\CsrException;
use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Activiteit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activiteit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activiteit[]    findAll()
 * @method Activiteit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Activiteit|null retrieveByUuid($UUID)
 */
class ActiviteitRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Activiteit::class);
	}

	/**
	 * @param Reeks $reeks
	 * @return Collection|Activiteit[]
	 */
	public function getKomendeActiviteiten(Reeks $reeks)
	{
		return $reeks->getActiviteiten()->filter(function (Activiteit $activiteit) {
			return $activiteit->magBekijken() && $activiteit->isInToekomst();
		});
	}

	public function delete(Activiteit $activiteit)
	{
		$em = $this->getEntityManager();

		$em->beginTransaction();
		try {
			$em->remove($activiteit);
			$em->flush();
			$em->commit();
		} catch (ORMException $ex) {
			$em->rollback();
			throw new CsrException($ex->getMessage());
		}
	}
}
