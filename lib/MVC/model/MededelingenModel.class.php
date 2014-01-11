<?php

require_once 'MVC/model/PagingModel.abstract.php';
require_once 'MVC/model/entity/Mededeling.class.php';

/**
 * MededelingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MededelingenModel extends PaginationModel {

	public function __construct() {
		parent::__construct('Mededeling');
	}

	public function fetch($where = null, array $params = array(), $assoc = false) {
		if (is_int($where)) {
			return parent::fetchOne('id = ?', array($where));
		}
		$list = $this->load($where, $params, 'prioriteit ASC, id DESC');
		if (!$assoc) {
			return $list;
		}
		$result = array();
		foreach ($list as $i => $mededeling) {
			$result[$mededeling->id] = $mededeling;
			unset($list[$i]);
		}
		return $result;
	}

	public function save(Mededeling &$mededeling) {
		$properties = $mededeling->getPersistingValues();
		if (is_int($mededeling->id) && $mededeling->id > 0) { // update existing
			$count = $this->update('id = :id', array(':id', $mededeling->id), $properties);
			if ($count !== 1) {
				throw new Exception('Update row count: ' . $count);
			}
		} else { // insert new
			$mededeling->id = $this->insert($properties);
		}
	}

	public function remove($id) {
		parent::delete('id=?', array($id));
	}

}

?>