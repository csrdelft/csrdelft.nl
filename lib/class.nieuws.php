<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.nieuws.php
# -------------------------------------------------------------------
# Verzorgt het opvragen en opslaan van nieuwsberichten.
# Wordt o.a. door NieuwsContent gebruikt
# -------------------------------------------------------------------



class Nieuws {

	private $_lid;
	private $_db;

	private $aantalTopBerichten;
	private $topBerichtenSpeling;
	private $standaardRank;

	function Nieuws(){
		$this->_lid=Lid::instance();
		$this->_db=MySql::instance();
	}

	public function setAantalTopBerichten($iAantal){ $this->aantalTopBerichten=(int)$iAantal; }
	public function setTopBerichtenSpeling($iSpeling){ $this->topBerichtenSpeling=(int)$iSpeling; }
	public function setStandaardRank($iRank){ $this->standaardRank=(int)$iRank; }
	public function getAantalTopBerichten(){ return $this->aantalTopBerichten; }
	public function getTopBerichtenSpeling(){ return $this->topBerichtenSpeling; }
	public function getStandaardRank(){ return $this->standaardRank; }

	# ophaelen nieuwsberichten
	# $iBerichtID == 0 -> alles ophaelen met een limiet van $this->_aantal;
	# $iBerichtID != 0 -> alleen opgegeven nummer
	public function getMessages($iBerichtID=0,$includeVerborgen=false,$limit=0){
		$iBerichtID=(int)$iBerichtID;
		//where clausule klussen
		$sWhereClause='';
		if(!$this->_lid->hasPermission('P_LOGGED_IN')){ $sWhereClause.="mededeling.prive!='1' AND "; }
		if(!$includeVerborgen){ $sWhereClause.="mededeling.verborgen!='1' AND "; }
		if($iBerichtID!=0){ $sWhereClause.="mededeling.id=".$iBerichtID." AND "; }
		$limit=(int)$limit;

		$sNieuwsQuery="
			SELECT
				mededeling.id as id,
				mededeling.datum as datum,
				mededeling.titel as titel,
				mededeling.tekst as tekst,
				mededeling.rank as rank,
				mededeling.uid as uid,
				mededeling.prive as prive,
				mededeling.verborgen as verborgen,
				mededeling.plaatje as plaatje,
				mededeling.categorie as categorie,
				mededelingcategorie.plaatje as categorieplaatje,
				mededelingcategorie.naam as categorienaam
			FROM
				mededeling
			LEFT JOIN
				mededelingcategorie ON( mededelingcategorie.id=mededeling.categorie )
			WHERE
				".$sWhereClause."
				mededeling.verwijderd='0'
			ORDER BY
				mededeling.datum DESC";
		if($limit!=0 AND $limit>0){
			$sNieuwsQuery.=' LIMIT 0,'.$limit;
		}
		$sNieuwsQuery.=';';
		$rNieuwsBerichten=$this->_db->query($sNieuwsQuery);
		if($iBerichtID!=0){
			return $this->_db->next($rNieuwsBerichten);
		}else{
			return $this->_db->result2array($rNieuwsBerichten);
		}
	}
	public function getMessage($iBerichtID, $includeVerborgen=false){ return $this->getMessages($iBerichtID, $includeVerborgen);	}

	public function getCategorieen(){
		$sCategorieQuery="
			SELECT
				id, naam
			FROM
				mededelingcategorie
			ORDER BY
				rank, id";
		$rCategorieen=$this->_db->query($sCategorieQuery);
		return $this->_db->result2array($rCategorieen);
	}

	public function getTop($aantal){
		$aantal=(int)$aantal;
		if($aantal <= 0){ return array(); }

		//where clausule klussen
		$sWhereClause='';
		if(!$this->_lid->hasPermission('P_LOGGED_IN')){ $sWhereClause.="mededeling.prive!='1' AND "; }

		$sQuery="
			SELECT
				mededeling.id as id,
				mededeling.datum as datum,
				mededeling.titel as titel,
				mededeling.tekst as tekst,
				mededeling.prive as prive,
				mededeling.plaatje as plaatje
			FROM
				mededeling
			WHERE
				".$sWhereClause."
				mededeling.verwijderd='0' AND
				mededeling.verborgen='0'
			ORDER BY
				mededeling.rank ASC,
				mededeling.datum DESC
			LIMIT
				0, ".$aantal.";";
		$rTop=$this->_db->query($sQuery);
		if($aantal==1){
			return $this->_db->next($rTop);
		} else {
			return $this->_db->result2array($rTop);
		}
	}

	//bericht toevoegen
	public function addMessage($titel, $tekst, $categorie, $rank=255, $prive=false, $verborgen=false, $plaatje=''){
		$datum=time();
		$titel=$this->_db->escape($titel);
		$tekst=$tekst;
		$categorie=(int)$categorie;
		$rank=(int)$rank;
		if($rank!=$this->getStandaardRank()){ $this->resetRank($rank); }
		if($prive){$prive=1; }else{ $prive=0; }
		if($verborgen){$verborgen=1; }else{ $verborgen=0; }
		$plaatje=trim($plaatje);
		$uid=$this->_lid->getUid();
		$sMessageQuery="
			INSERT INTO
				mededeling
			(
				datum, titel, categorie, tekst, rank, uid, prive, verborgen, plaatje
			) VALUES (
				".$datum.", '".$titel."', '".$categorie."', '".$tekst."', '".$rank."',
				'".$uid."', '".$prive."', '".$verborgen."', '".$plaatje."'
			);";
		return $this->_db->query($sMessageQuery);
	}
	public function setPlaatje($nieuwsID, $bestandsnaam=''){
		$bestandsnaam=$this->_db->escape($bestandsnaam);
		$sPlaatje="
			UPDATE
				mededeling
			SET
				plaatje='".$bestandsnaam."'
			WHERE
				id=".$nieuwsID."
			LIMIT 1;";
		return $this->_db->query($sPlaatje);
	}
	public function deleteMessage($iBerichtID){
		$iBerichtID=(int)$iBerichtID;
		$sMessageQuery="
			UPDATE
				mededeling
			SET
				verwijderd='1'
			WHERE
				id=".$iBerichtID."
			LIMIT 1;";
		return $this->_db->query($sMessageQuery);
	}
	public function editMessage($iBerichtID, $titel, $tekst, $categorie, $rank, $prive=false, $verborgen=false){
		$iBerichtID=(int)$iBerichtID;
		$titel=$this->_db->escape($titel);
		$tekst=$tekst;
		$rank=(int)$rank;
		if($rank!=$this->getStandaardRank()){ $this->resetRank($rank); }
		$categorie=(int)$categorie;
		if($prive){$prive=1; }else{ $prive=0; }
		if($verborgen){$verborgen=1; }else{ $verborgen=0; }
		$sMessageQuery="
			UPDATE
				mededeling
			SET
				titel='".$titel."',
				tekst='".$tekst."',
				categorie='".$categorie."',
				rank='".$rank."',
				prive='".$prive."',
				verborgen='".$verborgen."'
			WHERE
				id=".$iBerichtID."
			LIMIT 1;";
		return $this->_db->query($sMessageQuery);
	}
	public function isNieuwsMod(){ return $this->_lid->hasPermission('P_NEWS_MOD');}

	public function resize_plaatje($file) {
		list($owdt,$ohgt,$otype)=@getimagesize($file);
		switch($otype) {
			case 1:  $oldimg=imagecreatefromgif($file); break;
			case 2:  $oldimg=imagecreatefromjpeg($file); break;
			case 3:  $oldimg=imagecreatefrompng($file); break;
		}
		if($oldimg) {
			$newimg=imagecreatetruecolor(200, 200);
			if(imagecopyresampled($newimg, $oldimg, 0, 0, 0, 0, 200, 200, $owdt, $ohgt)){
				switch($otype) {
					case 1: imagegif($newimg,$file); break;
					case 2: imagejpeg($newimg,$file,90); break;
					case 3: imagepng($newimg,$file);  break;
				}
				imagedestroy($newimg);
			}else{
				//mislukt
			}
		}
	}

	private function resetRank($rank){
		$rank=(int)$rank;
		if($rank<=0 OR $rank>=$this->getStandaardRank())
			return;

		$sUpdateRankQuery="
			UPDATE
				mededeling
			SET
				rank='".$this->getStandaardRank()."'
			WHERE
				rank='".$rank."';";
		$this->_db->query($sUpdateRankQuery);
	}

	/*
	 * Geeft de id van de belangrijkste mededeling terug.
	 * De belangrijkste mededeling is het top 1-bericht en als deze
	 * niet bestaat, is het de nieuwste.
	 */
	public function getBelangrijksteMededelingId()
	{
		$sTop1Query="
			SELECT
				id
			FROM
				mededeling
			WHERE
				rank = '1' AND
				mededeling.verwijderd='0' AND
				mededeling.verborgen='0';
		";
		$rTop1=$this->_db->query($sTop1Query);
		if($this->_db->numRows($rTop1)==1){ // Indien er gewoon één resultaat is.
			$aTop1=$this->_db->next($rTop1);
			return (int)$aTop1['id'];
		} else {
			// Als er géén top1 is, of zelfs meerdere, dan gaan we zoeken naar de nieuwste mededeling.
			$sNieuwsteQuery="
				SELECT
					id
				FROM
					mededeling
				WHERE
					mededeling.verwijderd='0' AND
					mededeling.verborgen='0'
				ORDER BY
					datum DESC, id DESC
				LIMIT
					1;
			";
			$rNieuwste=$this->_db->query($sNieuwsteQuery);
			if($this->_db->numRows($rNieuwste)==1){ // Indien er gewoon één resultaat is.
				$aNieuwste=$this->_db->next($rNieuwste);
				return (int)$aNieuwste['id'];
			}
			else
			{
				// Indien er helemaal geen mededeling te vinden is, geven we 0 terug.
				return 0;
			}
		}
	}
}

?>
