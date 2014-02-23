<?php

require_once 'MVC/model/entity/CmsPagina.class.php';

/**
 * CmsPaginaModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bekijken of bewerken van CmsPaginas.
 */
class CmsPaginaModel extends PersistenceModel {

	public function __construct() {
		parent::__construct(new CmsPagina());
	}

	public function getAllePaginas() {
		$paginas = $this->find(null, array(), 'titel ASC');
		$result = array();
		foreach ($paginas as $pagina) {
			if ($pagina->magBekijken()) {
				$result[$pagina->naam] = $pagina;
			}
		}
		return $result;
	}

	public function getPagina($naam) {
		return $this->retrieveByPrimaryKey(array($naam));
	}

	public function newPagina($naam) {
		$pagina = new CmsPagina();
		$pagina->naam = $naam;
		$pagina->titel = $naam;
		$pagina->inhoud = $naam;
		$pagina->laatst_gewijzigd = date('Y-m-d H:i:s');
		$pagina->rechten_bekijken = 'P_NOBODY';
		$pagina->rechten_bewerken = 'P_ADMIN';
		return $pagina;
	}

}
