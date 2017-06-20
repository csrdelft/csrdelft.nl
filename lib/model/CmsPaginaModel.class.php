<?php
namespace CsrDelft\model;
use function CsrDelft\getDateTime;
use CsrDelft\model\entity\CmsPagina;
use CsrDelft\Orm\PersistenceModel;

/**
 * CmsPaginaModel.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Bekijken of bewerken van CmsPaginas.
 */
class CmsPaginaModel extends PersistenceModel {

	const ORM = CmsPagina::class;

	protected static $instance;

	/**
	 * @param $naam
	 *
	 * @return CmsPagina|false
	 */
	public static function get($naam) {
		return static::instance()->retrieveByPrimaryKey(array($naam));
	}

	public function getAllePaginas() {
		$paginas = $this->find(null, array(), null, 'titel ASC');
		$result = array();
		foreach ($paginas as $pagina) {
			if ($pagina->magBekijken()) {
				$result[$pagina->naam] = $pagina;
			}
		}
		return $result;
	}

	public function nieuw($naam) {
		$pagina = new CmsPagina();
		$pagina->naam = $naam;
		$pagina->titel = $naam;
		$pagina->inhoud = $naam;
		$pagina->laatst_gewijzigd = getDateTime();
		$pagina->rechten_bekijken = 'P_PUBLIC';
		$pagina->rechten_bewerken = 'P_ADMIN';
		return $pagina;
	}

}
