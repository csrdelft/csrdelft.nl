<?php


namespace CsrDelft\view\forum;


class NieuwForumPost {
	/**
	 * Forum waaronder dit topic valt
	 * @var int
	 */
	public $forum_id;

	/**
	 * Draad waar dit topic onder valt, null voor nieuw draad
	 * @var int|null
	 */
	public $draad_id = null;
	/**
	 * Titel, null als draad al bestaat
	 * @var string
	 */
	public $titel;

	/**
	 * Inhoud van het draad
	 * @var string
	 */
	public $forumBericht;
}
