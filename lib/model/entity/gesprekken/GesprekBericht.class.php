<?php

namespace CsrDelft\model\entity\gesprekken;

use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * GesprekBericht.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class GesprekBericht extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $bericht_id;
	/**
	 * Foreign key
	 * @var int
	 */
	public $gesprek_id;
	/**
	 * DateTime
	 * @var string
	 */
	public $moment;
	/**
	 * Lidnummer auteur
	 * @var string
	 */
	public $auteur_uid;
	/**
	 * Bericht inhoud
	 * @var string
	 */
	public $inhoud;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'bericht_id' => array(T::Integer, false, 'auto_increment'),
		'gesprek_id' => array(T::Integer),
		'moment' => array(T::DateTime),
		'auteur_uid' => array(T::UID),
		'inhoud' => array(T::Text)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('bericht_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'gesprek_berichten';

	public function getAuteurFormatted() {
		$profiel = ProfielModel::get($this->auteur_uid);
		return '<img class="cd-user-avatar float-left" src="/plaetjes/pasfoto/' . $profiel->getPasfotoPath(true) . '"/><div class="dikgedrukt">' . $profiel->getLink('volledig') . '</div>';
	}

	public function getFormatted($previous, $max = false) {
		if ($previous AND $previous->auteur_uid === $this->auteur_uid) {
			$auteur = '';
		} else {
			$auteur = $this->getAuteurFormatted();
		}
		$moment = '<span data-order="' . $this->moment . '" class="lichtgrijs float-right">' . reldate($this->moment) . '</span>';

		if (is_int($max)) {
			$inhoud = mb_substr($this->inhoud, 0, $max);
			if (mb_strlen($this->inhoud) > $max) {
				$inhoud .= '...';
			}
		} else {
			$inhoud = $this->inhoud;
		}

		return $moment . $auteur . $inhoud;
	}

}
