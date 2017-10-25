<?php

namespace CsrDelft\view;

/**
 * FlotTimeSeries.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FlotTimeSeries extends JsonResponse {

	public function getJson($model) {
		$array = array();
		foreach ($model as $label => $data) {
			$entry = array();
			foreach ($data as $row) {
				if (isset($row['timestamp'], $row['count'])) {
					$entry[] = array((int)$row['timestamp'] * 1000, (int)$row['count']);
				} else {
					//var_dump($row);
				}
			}
			$array[] = array(
				'label' => $label,
				'data' => $entry
			);
		}
		return parent::getJson($array);
	}

}
