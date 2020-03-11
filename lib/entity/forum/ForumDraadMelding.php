<?php

namespace CsrDelft\entity\forum;

use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumDraadMelding.class.php
 * Leden kunnen meldingen krijgen voor een forumdraad
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\forum\ForumDradenMeldingRepository")
 * @ORM\Table("forum_draden_volgen")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class ForumDraadMelding {

	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $draad_id;
	/**
	 * Lidnummer
	 * Shared primary key
	 * Foreign key
	 * @var string
	 * @ORM\Column(type="string", length=4)
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * Volgniveau
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $niveau = 'altijd';
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'draad_id' => array(T::Integer),
		'uid' => array(T::UID),
		'niveau' => array(T::Enumeration, false, ForumDraadMeldingNiveau::class)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('draad_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'forum_draden_volgen';

}
