<?php

namespace CsrDelft\repository;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\CmsPagina;
use CsrDelft\model\OrmTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Bekijken of bewerken van CmsPaginas.
 * @method CmsPagina[]    ormFind($criteria = null, $criteria_params = [], $group_by = null, $order_by = null, $limit = null, $start = 0)
 * @method CmsPagina|null doctrineFind($id, $lockMode = null, $lockVersion = null)
 * @method CmsPagina|null find($id, $lockMode = null, $lockVersion = null)
 * @method CmsPagina|null findOneBy(array $criteria, array $orderBy = null)
 * @method CmsPagina[]    findAll()
 * @method CmsPagina[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CmsPaginaRepository extends ServiceEntityRepository {
	use OrmTrait;

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, CmsPagina::class);
	}

	/**
	 * @return CmsPagina[]
	 */
	public function getAllePaginas() {
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
	public function nieuw($naam) {
		$pagina = new CmsPagina();
		$pagina->naam = $naam;
		$pagina->titel = $naam;
		$pagina->inhoud = $naam;
		$pagina->laatst_gewijzigd = date_create();
		$pagina->rechten_bekijken = P_PUBLIC;
		$pagina->rechten_bewerken = P_ADMIN;
		return $pagina;
	}

}
