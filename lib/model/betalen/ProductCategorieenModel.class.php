<?php

/**
 * ProductCategorieenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductCategorieenModel extends PersistenceModel {

	const ORM = 'ProductCategorie';
	const DIR = 'betalen/';

	protected static $instance;

	public function newProductCategorie($titel, $beheer_rechten) {
		$categorie = new ProductCategorie();
		$categorie->titel = $titel;
		$categorie->beheer_rechten = $beheer_rechten;
		return $categorie;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity $categorie
	 * @return int categorie_id
	 */
	public function create(PersistentEntity $categorie) {
		$categorie->categorie_id = (int) parent::create($categorie);
		return $categorie->categorie_id;
	}

}
