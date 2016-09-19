<?php

/**
 * PrullenbakView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class PrullenbakTable extends DataTable {

	public function __construct(PrullenbakModel $model) {
		parent::__construct($model::ORM, '/prullenbak/data', 'Prullenbak', 'model_class');

		$this->hideColumn('object_id', false);

		$restore = new DataTableKnop('>= 1', $this->dataTableId, '/prullenbak/restore', 'post confirm', 'Terugzetten (recursief)', 'Terugzetten', 'undo');
		$this->addKnop($restore);

		$delete = new DataTableKnop('>= 1', $this->dataTableId, '/prullenbak/delete', 'post confirm', 'Verwijderen (recursief)', 'Permanent verwijderen', 'delete');
		$this->addKnop($delete);
	}

}

class PrullenbakData extends DataTableResponse {

	public function getJson($entity) {
		$array = $entity->jsonSerialize();

		$attributes = RemovedAttributesModel::instance()->find('object_id = ?', array($entity->object_id));
		$array['details'] = '<table>';
		foreach ($attributes as $attribute) {
			$array['details'] .= '<tr><td class="dikgedrukt">' . $attribute->name . ':</td><td>' . $attribute->value . '</td></tr>';
		}
		$array['details'] .= '</table>';

		$array['removed_by_uid'] = ProfielModel::getLink($entity->removed_by_uid, 'civitas');

		return parent::getJson($array);
	}

}
