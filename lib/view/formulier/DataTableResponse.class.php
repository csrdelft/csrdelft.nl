<?php

/**
 * DataTableResponse.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class DataTableResponse extends JsonResponse {

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
		foreach ($this->model as $model) {
			if ($comma) {
				echo ",\n";
			} else {
				$comma = true;
			}
			$json = $this->getJson($model);
			if ($json) {
				echo $json;
			} else {
				$comma = false;
			}
		}
		echo "\n]}";
	}

}

class RemoveRowsResponse extends DataTableResponse {

	public function getJson($model) {
		return parent::getJson(array(
					'UUID'	 => ( method_exists($model, 'getUUID') ? $model->getUUID() : $model ),
					'remove' => true
		));
	}

}
