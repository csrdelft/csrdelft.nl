<?php

/**
 * GoedkeurenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GoedkeurenTable extends DataTable {

	public function __construct(GoedkeurenModel $model) {
		parent::__construct($model::ORM, '/goedkeuren/data', 'Goedkeuren', 'model_class');

		$this->hideColumn('object_id', false);

		$approve = new DataTableKnop('>= 1', $this->dataTableId, '/goedkeuren/approve', 'post confirm', 'Goedkeuren (recursief)', 'Goedkeuren', 'tick');
		$this->addKnop($approve);

		$delete = new DataTableKnop('>= 1', $this->dataTableId, '/goedkeuren/delete', 'post confirm', 'Verwijderen (recursief)', 'Permanent verwijderen', 'delete');
		$this->addKnop($delete);
	}

}

class GoedkeurenData extends DataTableResponse {

	public function getJson($entity) {
		$array = $entity->jsonSerialize();

		$attributes = ApproveAttributesModel::instance()->find('object_id = ?', array($entity->object_id));
		$array['details'] = '<table>';
		foreach ($attributes as $attribute) {
			$array['details'] .= '<tr><td class="dikgedrukt">' . $attribute->name . ':</td><td>' . $attribute->value . '</td></tr>';
		}
		$array['details'] .= '</table>';

		$array['removed_by_uid'] = ProfielModel::getLink($entity->removed_by_uid, 'civitas');

		return parent::getJson($array);
	}

}
