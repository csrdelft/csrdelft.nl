<?php

namespace CsrDelft\controller\api;


/**
 * @author J. Rijsdijk <jorairijsdijk@gmail.com>
 * @date 04/11/2017
 */
class ApiSponsorlinksController {
	/**
	 * @url GET /
	 */
	public function getSponsorlinks() {
		$json = file(DATA_PATH . 'sponsorlinks.json', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		return json_decode($json[0], true);
	}

	/**
     * @url GET /timestamp
     */
	public function getTimestamp() {
	    $timestamp = filemtime(DATA_PATH . 'sponsorlinks.json');
	    return $timestamp;
    }
}
