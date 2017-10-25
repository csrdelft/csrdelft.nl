<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\CsrException;
use CsrDelft\MijnSqli;

/**
 * BiebRubriek.class.php  |  Gerrit Uitslag
 *
 * rubriek
 *
 */
class BiebRubriek {

	private $id = 0;
	private $rubriek = array(); //de rubrieken

	/*
	 * constructor
	 * @param $init	array met de rubrieken
	 * 				int rubriekid
	 */

	public function __construct($init) {
		if (is_array($init)) {
			$this->rubriek = $init;
		} else {
			$db = MijnSqli::instance();
			$query = "
				SELECT c2.id, c1.categorie AS cat1, c2.categorie AS cat2
				FROM biebcategorie c1, biebcategorie c2
				WHERE c2.p_id = c1.id
				AND c1.p_id =0
				AND c2.id = " . (int)$init . ";";
			$categorie = $db->getRow($query);

			if (is_array($categorie)) {
				$this->id = $categorie['id'];
				$this->rubriek = array($categorie['cat1'], $categorie['cat2']);
			} else {
				throw new CsrException('__contruct() mislukt. Bestaat de rubriek wel? ' . $db->error());
			}
		}
	}

	public function __toString() {
		return $this->getRubrieken();
	}

	public function getId() {
		return $this->id;
	}

	public function getRubriekArray() {
		return $this->rubriek;
	}

	public function getRubrieken() {
		return implode(" - ", $this->rubriek);
	}

	public function getRubriek() {
		return $this->rubriek[1];
	}

	/*
	 * geeft alle rubrieken
	 *
	 * @param 	$samenvoegen 	true:geeft een string,
	 * 							false: raw array
	 * 			$short		true:array with rubriekids as key
	 * 						false: array with subarray('id'=> int, 'cat' => 'string')
	 * @return	false&(true|false): array with subarrays('id'=> int , 'cat1'=> 'string1', 'cat2=> etc..)
	 * 			true&false: array met  id => 'cat - subcat - subsubcat'
	 * 			true&true: array met  array('id'=> int, 'cat' => 'cat - subcat - subsubcat')
	 * 			of een lege array
	 */

	public static function getAllRubrieken($samenvoegen = false, $short = false) {
		$db = MijnSqli::instance();
		$query = "
			SELECT c2.id, c1.categorie AS cat1, c2.categorie AS cat2
			FROM biebcategorie c1, biebcategorie c2
			WHERE c2.p_id = c1.id
			AND c1.p_id =0
			ORDER BY c2.id ASC;";
		$result = $db->query($query);
		echo $db->error();
		if ($db->numRows($result) > 0) {
			while ($categorie = $db->next($result)) {
				if ($samenvoegen) {
					$samengevoegderubrieken = implode(" - ", array($categorie['cat1'], $categorie['cat2']));
					if ($short) {
						$categorien[$categorie['id']] = $samengevoegderubrieken;
					} else {
						$categorien[] = array(
							'id' => $categorie['id'],
							'cat' => $samengevoegderubrieken
						);
					}
				} else {
					$categorien[] = $categorie;
				}
			}
			return $categorien;
		} else {
			return array();
		}
	}

	/*
	 * geeft alle rubriekids
	 *
	 * @return	array met id's
	 * 			of een lege array
	 */

	public static function getAllRubriekIds() {
		$db = MijnSqli::instance();
		$query = "
			SELECT id
			FROM biebcategorie;";
		$result = $db->query($query);
		echo $db->error();
		if ($db->numRows($result) > 0) {
			while ($categorieId = $db->next($result)) {
				$categorieIds[] = $categorieId['id'];
			}
			sort($categorieIds);
			return array_filter($categorieIds);
		} else {
			return array();
		}
	}

}
