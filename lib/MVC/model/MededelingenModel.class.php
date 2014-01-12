<?php

require_once 'MVC/mededeling/Mededeling2.class.php';

/**
 * MededelingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MededelingenModel extends PaginationModel {

	public function load($where = null, array $params = array(), $assoc = false) {
		if (is_int($where)) {
			return $this->get('id = ?', array($where));
		}
		$list = $this->select($where, $params, 'prioriteit ASC, id DESC');
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

	public function save(MededelingNew &$mededeling) {
		$properties = $mededeling->getPersistingValues();
		if (is_int($mededeling->id) && $mededeling->id > 0) { // update existing
			$count = $this->update($properties, 'id = :id', array(':id' => $mededeling->id));
			if ($count !== 1) {
				throw new Exception('Update row count: ' . $count);
			}
		} else { // insert new
			$mededeling->id = $this->insert($properties);
		}
	}

	public function remove($id) {
		$this->delete('id = ?', array($id));
	}

}
