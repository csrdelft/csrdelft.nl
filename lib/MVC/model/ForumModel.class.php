<?php

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ForumModel extends PaginationModel {

	protected static $instance;
	protected static $orm = 'ForumItem';

	public function getForumItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newForumItem($parent_id) {
		$item = new ForumItem();
		$item->parent_id = intval($parent_id);
		$item->prioriteit = 0;
		$item->link = '/';
		$item->rechten_bekijken = 'P_NOBODY';
		$item->zichtbaar = true;
		return $item;
	}

	public function removeForumItem($id) {
		return $this->deleteByPrimaryKey(array($id));
	}

}
