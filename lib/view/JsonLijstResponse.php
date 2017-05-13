<?php
/**
 * JsonLijstResponse.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view;

class JsonLijstResponse extends JsonResponse {

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo "[\n";
		$comma = false;
		foreach ($this->model as $item) {
			if ($comma) {
				echo ",\n";
			} else {
				$comma = true;
			}
			$json = $this->getJson($item);
			if ($json) {
				echo $json;
			} else {
				$comma = false;
			}
		}
		echo "\n]";
	}

}