<?php

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ForumModel extends PersistenceModel {

	protected static $instance;
	protected static $orm = 'ForumCategorie';

	public function getForum() {
		$delen = ForumDelenModel::instance()->getAlleForumDelenPerCategorie();
		$categorien = $this->find(null, array(), 'volgorde');
		foreach ($categorien as $cat) {
			if (array_key_exists($cat->categorie_id, $delen)) {
				$cat->setForumDelen($delen[$cat->categorie_id]);
				unset($delen[$cat->categorie_id]);
			} else {
				$cat->setForumDelen(array());
			}
		}
		return $categorien;
	}

}

class ForumDelenModel extends PersistenceModel {

	protected static $instance;
	protected static $orm = 'ForumDeel';

	public function getAlleForumDelenPerCategorie() {
		return array_group_by('categorie_id', $this->find(null, array(), 'volgorde'));
	}

}

/**
 * TODO
 */
class ForumTopicsModel extends PaginationModel {

	protected static $instance;
	protected static $orm = 'ForumTopic';

}

class ForumPostsModel extends PaginationModel {

	protected static $instance;
	protected static $orm = 'ForumPost';

	public function newForumPost($topic_id) {
		$post = new ForumPost();
		$post->topic_id = (int) $topic_id;
		//TODO
		return $post;
	}

	public function removeForumPost($id) {
		$post = $this->retrieveByPrimaryKey(array($id));
		$post->verwijderd = true;
		$this->update($post);
		return $post;
	}

}
