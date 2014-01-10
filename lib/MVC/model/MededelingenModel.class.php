<?php

require_once 'MVC/model/PagingModel.class.php';

/**
 * MededelingenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class MededelingenModel extends PagingModel {

	public function __construct() {
		parent::__construct('Mededeling', 'mededelingen');
		//$this->create_table(array('id'));
	}

	public function getOne($id) {
		$this->load('id=?', array($id), null, 1);
	}

	public function getAll($where = null, array $values = array(), $assoc = false) {
		$list = $this->load($where, $values, 'prioriteit ASC, id DESC');
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

	public function save(Mededeling $mededeling) {
		$properties = get_object_vars($mededeling);
		unset($properties['id']); // never change primary key
		if (is_int($mededeling->id) && $mededeling->id > 0) {
			$count = $this->update('id = :id', array(':id', $mededeling->id), $properties);
			if ($count !== 1) {
				throw new Exception('Update row count: ' . $count);
			}
			return $mededeling;
		}
		$mededeling->id = $this->insert($properties);
		return $mededeling;
	}

	public function delete($id) {
		parent::delete('id=?', array($id));
	}

}

?>