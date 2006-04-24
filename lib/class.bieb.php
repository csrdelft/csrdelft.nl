<?php

class Bieb {

	var $_lid;
	var $_db;
	
	function Bieb(&$lid, &$db) {
		$this->_lid =& $lid;
		$this->_db =& $db;
	}
	
	# haalt alle boeken op waarvoor een exemplaar bestaat
	function getBoekenAanwezig($sorterenOp = '') {
		# beveiliging op inhoud sorteervariabele
		switch($sorterenOp) {
			case 'auteur':
			case 'titel':
			case 'categorie':
			case 'taal':
			case 'aantal':
				# goedgekeurd, mag verder
				break;
			case '':
				# goedgekeurd, mag verder
				$sorterenOp = 'auteur.naam';
				break;
				
			default:
				# komt niet in lijst voor; afhaken
				return false;
		}
		
		$query="
			SELECT
				boek.id AS boek_id,
				boek.titel AS titel,
				boek.taal AS taal,
				boek.isbn AS isbn,
				boek.uitgavejaar AS uitgavejaar,
				auteur.id AS auteur_id,
				auteur.naam AS auteur,
				categorie.categorie AS categorie,
				(
					SELECT COUNT(*)
					FROM biebexemplaar
					WHERE boek_id = boek.id
				)  AS aantal
			FROM
				biebboek boek,
				biebauteur auteur,
				biebcategorie categorie
			WHERE
				boek.auteur_id = auteur.id AND
				boek.categorie_id = categorie.id AND
				boek.id IN (
					SELECT boek_id
					FROM biebexemplaar
				)
			ORDER BY
				$sorterenOp
		";
		
		$result=$this->_db->query($query);
		return $this->_db->result2array($result); 
	}
	
	# haalt alle boeken op waarvoor een exemplaar bestaat
	function getBoekenAanwezigVanAuteur($auteur_id, $sorterenOp = '') {
		$auteur_id = (int) $auteur_id;
		
		# beveiliging op inhoud sorteervariabele
		switch($sorterenOp) {
			case 'auteur':
			case 'titel':
			case 'categorie':
			case 'taal':
			case 'aantal':
				# goedgekeurd, mag verder
				break;
			case '':
				# goedgekeurd, mag verder
				$sorterenOp = 'auteur.naam';
				break;
				
			default:
				# komt niet in lijst voor; afhaken
				return false;
		}
		
		$query="
			SELECT
				boek.id AS boek_id,
				boek.titel AS titel,
				boek.taal AS taal,
				boek.isbn AS isbn,
				boek.uitgavejaar AS uitgavejaar,
				auteur.id AS auteur_id,
				auteur.naam AS auteur,
				categorie.categorie AS categorie,
				(
					SELECT COUNT(*)
					FROM biebexemplaar
					WHERE boek_id = boek.id
				)  AS aantal
			FROM
				biebboek boek,
				biebauteur auteur,
				biebcategorie categorie
			WHERE
				auteur.id = $auteur_id AND
				boek.auteur_id = auteur.id AND
				boek.categorie_id = categorie.id AND
				boek.id IN (
					SELECT boek_id
					FROM biebexemplaar
				)
			ORDER BY
				$sorterenOp
		";
		
		$result=$this->_db->query($query);
		return $this->_db->result2array($result);
	}
	
	/*
	
				exemplaar.id AS exemplaar_id,
				exemplaar.eigenaaruid AS eigenaar_uid,
				exemplaar.uitgeleenduid AS uitgeleend_uid,
				exemplaar.toegevoegd AS toegevoegd
	*/
}
?>
