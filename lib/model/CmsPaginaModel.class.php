<?php

/**
 * CmsPaginaModel.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bekijken of bewerken van CmsPaginas.
 */
class CmsPaginaModel extends PersistenceModel {

	const orm = 'CmsPagina';

	protected static $instance;

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
		$pagina->laatst_gewijzigd = getDateTime();
		$pagina->rechten_bekijken = 'P_PUBLIC';
		$pagina->rechten_bewerken = 'P_ADMIN';
		return $pagina;
	}

}
