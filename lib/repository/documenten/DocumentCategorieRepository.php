<?php

namespace CsrDelft\repository\documenten;

use CsrDelft\entity\documenten\DocumentCategorie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * Class DocumentCategorieModel.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @method DocumentCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentCategorie[]    findAll()
 * @method DocumentCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentCategorieRepository extends AbstractRepository
{


	/**
	 * @param $id
	 *
	 * @return DocumentCategorie|null
	 */
	public function get($id)
	{
		return $this->find($id);
	}

	public function findMetSchijfrechtenVoorLid()
	{
		return array_filter(
			$this->findAll(),
			fn($categorie) => $this->security->isGranted($categorie->schrijfrechten)
		);
	}
}
