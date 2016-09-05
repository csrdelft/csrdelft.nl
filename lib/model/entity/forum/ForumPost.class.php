<?php

/**
 * ForumPost.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een forumpost zit in een ForumDraad.
 * 
 */
class ForumPost extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $post_id;
	/**
	 * Deze post is van dit draadje
	 * @var int
	 */
	public $draad_id;
	/**
	 * Uid van auteur
	 * @var string
	 */
	public $uid;
	/**
	 * Tekst
	 * @var string
	 */
	public $tekst;
	/**
	 * Datum en tijd van aanmaken
	 * @var string
	 */
	public $datum_tijd;
	/**
	 * Datum en tijd van laatste bewerking
	 * @var string
	 */
	public $laatst_gewijzigd;
	/**
	 * Bewerking logboek
	 * @var string
	 */
	public $bewerkt_tekst;
	/**
	 * Verwijderd
	 * @var boolean
	 */
	public $verwijderd;
	/**
	 * IP adres van de auteur
	 * @var string
	 */
	public $auteur_ip;
	/**
	 * Wacht op goedkeuring
	 * @var boolean
	 */
	public $wacht_goedkeuring;
	/**
	 * Reden van wegfilteren
	 * @var string
	 */
	public $gefilterd;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'post_id'			 => array(T::UnsignedInteger, false, 'auto_increment'),
		'draad_id'			 => array(T::UnsignedInteger),
		'uid'				 => array(T::UID),
		'tekst'				 => array(T::Text),
		'datum_tijd'		 => array(T::DateTime),
		'laatst_gewijzigd'	 => array(T::DateTime),
		'bewerkt_tekst'		 => array(T::Text, true),
		'verwijderd'		 => array(T::Boolean),
		'auteur_ip'			 => array(T::String),
		'wacht_goedkeuring'	 => array(T::Boolean)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('post_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_posts';

	public function getForumDraad() {
		return ForumDradenModel::get($this->draad_id);
	}

	public function magCiteren() {
		return LoginModel::mag('P_LOGGED_IN') AND $this->getForumDraad()->magPosten();
	}

	public function magBewerken() {
		$draad = $this->getForumDraad();
		if ($draad->magModereren()) {
			return true;
		}
		if (!$draad->magPosten()) {
			return false;
		}
		return $this->uid === LoginModel::getUid() AND LoginModel::mag('P_LOGGED_IN');
	}

}
