<?php

namespace CsrDelft\repository;

use CsrDelft\entity\CmsPagina;
use Doctrine\Persistence\ManagerRegistry;

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
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, CmsPagina::class);
	}

	/**
	 * @return CmsPagina[]
	 */
	public function getAllePaginas()
	{
		/** @var CmsPagina[] $paginas */
		$paginas = $this->findBy([], ['titel' => 'ASC']);
		$result = [];
		foreach ($paginas as $pagina) {
			if ($pagina->magBekijken()) {
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
	public function nieuw($naam)
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
