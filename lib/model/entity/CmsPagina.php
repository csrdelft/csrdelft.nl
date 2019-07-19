<?php

namespace CsrDelft\model\entity;

use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * CmsPagina.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Content Management System Paginas zijn statische pagina's die via de front-end kunnen worden gewijzigd.
 */
class CmsPagina extends PersistentEntity {

	/**
	 * Primary key
	 * @var string
	 */
	public $naam;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Inhoud
	 * @var string
	 */
	public $inhoud;
	/**
	 * DateTime
	 * @var string
	 */
	public $laatst_gewijzigd;
	/**
	 * Permissie voor tonen
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Link
	 * @var string
	 */
	public $rechten_bewerken;
	/**
	 * Inline HTML
	 * @var boolean
	 */
	public $inline_html;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'naam' => array(T::StringKey),
		'titel' => array(T::String),
		'inhoud' => array(T::LongText),
		'laatst_gewijzigd' => array(T::DateTime),
		'rechten_bekijken' => array(T::String),
		'rechten_bewerken' => array(T::String),
		'inline_html' => array(T::Boolean)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('naam');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'cms_paginas';

	/**
	 * @return bool
	 */
	public function magBekijken() {
		return LoginModel::mag($this->rechten_bekijken);
	}

	/**
	 * @return bool
	 */
	public function magBewerken() {
		return LoginModel::mag($this->rechten_bewerken);
	}

	/**
	 * @return bool
	 */
	public function magRechtenWijzigen() {
		return LoginModel::mag(P_ADMIN);
	}

	/**
	 * @return bool
	 */
	public function magVerwijderen() {
		return LoginModel::mag(P_ADMIN);
	}

}
