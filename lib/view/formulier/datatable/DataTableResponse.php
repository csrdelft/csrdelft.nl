<?php
/**
 * DataTableResponse.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\formulier\datatable;

use CsrDelft\view\JsonResponse;

abstract class DataTableResponse extends JsonResponse {

	public $autoUpdate = false;
	public $modal = null;

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo "{\n";
		echo '"modal":' . json_encode($this->modal) . ",\n";
		echo '"autoUpdate":' . json_encode($this->autoUpdate) . ",\n";
		echo '"lastUpdate":' . json_encode(time() - 1) . ",\n";
		echo '"data":[' . "\n";
		$comma = false;
		foreach ($this->model as $entity) {
			if ($comma) {
				echo ",\n";
			} else {
				$comma = true;
			}
			$json = $this->getJson($entity);
			if ($json) {
				echo $json;
			} else {
				$comma = false;
			}
		}
		echo "\n]}";
	}

}