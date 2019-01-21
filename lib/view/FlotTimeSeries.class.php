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
		$array = [];
		foreach ($model as $label => $data) {
			$entry = [];
			foreach ($data as $row) {
				if (isset($row['timestamp'], $row['count'])) {
					$entry[] = ["key" => $label, "x" => (int)$row['timestamp'] * 1000, "y" => (int)$row['count']];
				} else {
					//var_dump($row);
				}
			}
			$array[] = $entry;
		}
		return parent::getJson(array_merge(...$array));
	}

}
