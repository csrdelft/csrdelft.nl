<?php

namespace CsrDelft\repository;

use CsrDelft\common\Security\Voter\Entity\CmsPaginaVoter;
use CsrDelft\entity\CmsPagina;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Bekijken of bewerken van CmsPaginas.
 * @method CmsPagina|null find($id, $lockMode = null, $lockVersion = null)
 * @method CmsPagina|null findOneBy(array $criteria, array $orderBy = null)
 * @method CmsPagina[]    findAll()
 * @method CmsPagina[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CmsPaginaRepository extends AbstractRepository
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(ManagerRegistry $registry, Security $security)
	{
		parent::__construct($registry, CmsPagina::class);
		$this->security = $security;
	}

	/**
	 * @return CmsPagina[]
	 */
	public function getAllePaginas(): array
	{
		/** @var CmsPagina[] $paginas */
		$paginas = $this->findBy([], ['titel' => 'ASC']);
		$result = [];
		foreach ($paginas as $pagina) {
			// TODO: Deze security check zou later moeten worden gedaan.
			if ($this->security->isGranted(CmsPaginaVoter::BEKIJKEN, $pagina)) {
				$result[$pagina->naam] = $pagina;
			}
		}
		return $result;
	}

	/**
	 * @param string $naam
	 *
	 * @return CmsPagina
	 */
	public function nieuw($naam): CmsPagina
	{
		$pagina = new CmsPagina();
		$pagina->naam = $naam;
		$pagina->titel = $naam;
		$pagina->inhoud = $naam;
		$pagina->laatstGewijzigd = date_create_immutable();
		$pagina->rechtenBekijken = P_PUBLIC;
		$pagina->rechtenBewerken = P_ADMIN;
		return $pagina;
	}
}
