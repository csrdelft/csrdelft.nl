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

	public function maakProductCategorie($titel, $beheer_rechten) {
		$categorie = new ProductCategorie();
		$categorie->titel = $titel;
		$categorie->beheer_rechten = $beheer_rechten;
		$categorie->categorie_id = (int) $this->create($categorie);
		return $categorie;
	}

}
