<?php

namespace CsrDelft\controller\api;

use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumPost;
use CsrDelft\model\forum\ForumDradenGelezenModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\bbcode\CsrBB;
use Jacwright\RestServer\RestException;

class ApiForumController {

	/**
	 * @return boolean
	 */
	public function authorize() {
		return ApiAuthController::isAuthorized() && LoginModel::mag(P_OUDLEDEN_READ);
	}

	/**
	 * @url GET /recent
	 * @param int offset
	 * @param int limit
	 * @return ForumDraad[]
	 */
	public function getRecent() {
		$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT) ?: 0;
		$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10;

		$draden = ForumDradenModel::instance()->getRecenteForumDraden($limit, null, false, $offset, true);

		foreach ($draden as $draad) {
			$draad->ongelezen = $draad->getAantalOngelezenPosts();
			$draad->laatste_post = ForumPostsModel::instance()->get($draad->laatste_post_id);
			$draad->laatste_wijziging_naam = ProfielModel::getNaam($draad->laatste_wijziging_uid, 'civitas');
		}

		return array('data' => array_values($draden));
	}

	/**
	 * @url GET /onderwerp/$id
	 * @param int offset
	 * @param int limit
	 * @return ForumPost[]
	 */
	public function getOnderwerp($id) {
		$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT) ?: 0;
		$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10;

		try {
			$draad = ForumDradenModel::instance()->get((int)$id);
		} catch (\Exception $e) {
			throw new RestException(404);
		}

		if (!$draad->magLezen()) {
			throw new RestException(403);
		}

		ForumDradenGelezenModel::instance()->setWanneerGelezenDoorLid($draad, time());

		$posts = ForumPostsModel::instance()->prefetch('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($id), null, 'datum_tijd DESC', $limit, $offset);

		// Most recent first
		$posts = array_reverse($posts);

		foreach ($posts as $post) {
			$post->uid_naam = ProfielModel::getNaam($post->uid, 'civitas');
			$post->tekst = CsrBB::parseLight($post->tekst);
		}

		return array('data' => $posts);
	}

}
