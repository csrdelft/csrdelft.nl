<?php

namespace CsrDelft\controller\api;


/**
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @date 04/11/2017
 */
class ApiSponsorkliksController {
	/**
	 * @url GET /
	 */
	public function getSponsorkliks() {
		$json = file(DATA_PATH . 'sponsorkliks.json', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		return ['data' => json_decode($json[0], true)];
	}
}
