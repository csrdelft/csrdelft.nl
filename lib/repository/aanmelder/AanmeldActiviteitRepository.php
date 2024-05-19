<?php

namespace CsrDelft\repository\aanmelder;

use CsrDelft\common\CsrException;
use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\entity\aanmelder\Reeks;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method AanmeldActiviteit|null find($id, $lockMode = null, $lockVersion = null)
 * @method AanmeldActiviteit|null findOneBy(array $criteria, array $orderBy = null)
 * @method AanmeldActiviteit[]    findAll()
 * @method AanmeldActiviteit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method AanmeldActiviteit|null retrieveByUuid($UUID)
 */
class AanmeldActiviteitRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, AanmeldActiviteit::class);
	}

	/**
	 * @param Reeks $reeks
	 * @return Collection|AanmeldActiviteit[]
	 */
	public function getKomendeActiviteiten(Reeks $reeks)
	{
		return $reeks
			->getActiviteiten()
			->filter(function (AanmeldActiviteit $activiteit): bool {
				return $activiteit->magBekijken() && $activiteit->isInToekomst();
			});
	}

	public function delete(AanmeldActiviteit $activiteit)
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

	public function sluit(AanmeldActiviteit $activiteit, bool $sluit = true)
	{
		try {
			$activiteit->setGesloten($sluit);
			$this->getEntityManager()->flush();
		} catch (Exception $e) {
			throw new CsrException($e->getMessage());
		}
	}
}
