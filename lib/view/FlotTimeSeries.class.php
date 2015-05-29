<?php

/**
 * FlotTimeSeries.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class FlotTimeSeries extends JsonResponse {

	public function getJson($data) {
		$array = array();
		foreach ($data as $entry) {
			$array[] = array((int) $entry['timestamp'], (int) $entry['count']);
		}
		return json_encode($array);
	}

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo '[' . "\n";
		$comma = false;
		foreach ($this->model as $label => $data) {
			if ($comma) {
				echo ',';
			} else {
				$comma = true;
			}
			echo <<<JSON
{
	"label": "{$label}",
	"data":
JSON;
			echo $this->getJson($data);
			echo "\n}";
		}
		echo "\n]";
	}

}
