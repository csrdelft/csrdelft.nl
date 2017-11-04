<?php
/**
 * Created by IntelliJ IDEA.
 * User: jorai
 * Date: 11/4/17
 * Time: 5:34 PM
 */

namespace CsrDelft\controller\api;


class ApiSponsorkliksController {
	/**
	 * @url GET /
	 */
	public function getSponsorkliks() {
		$json = file(DATA_PATH . 'sponsorkliks.json', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		return ['data' => json_decode($json[0], true)];
	}
}
