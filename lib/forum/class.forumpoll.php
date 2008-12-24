<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.forum.php
# -------------------------------------------------------------------
# Forum databaseklasse
# -------------------------------------------------------------------


class ForumPoll {
	
	var $_db;
	var $_lid;
	var $_forum;
	
	
	var $_maxPollOptions=10;
	
	
	function ForumPoll(&$forum){
		# databaseconnectie openen
		$this->_lid=Lid::get_lid();
		$this->_db=MySql::get_MySql();
		$this->_forum =& $forum;
	}
	function getTopicID(){
		return $this->_forum->getID(); 
	}
	function getPollVraag(){
		return $this->_forum->getTitel();
	}
	function getPollOpties(){
		$sOptiesQuery="
			SELECT
				id, optie, stemmen
			FROM
				forum_poll
			WHERE
				topicID=".$this->_forum->getID()."
			ORDER BY
				id ;";
		$rOptiesResult=$this->_db->query($sOptiesQuery);
		if($this->_db->numRows($rOptiesResult)!=0){
			//zoo, opties in een array rossen
			while($aOptieData=$this->_db->next($rOptiesResult)){
				$aOpties[]=array(
					'id' => $aOptieData['id'],
					'topicID' => $this->_forum->getID(),
					'optie' => $aOptieData['optie'],
					'stemmen' => $aOptieData['stemmen'] );
			}
			return $aOpties;
		}else{
			return false;
		}
	}
	function getLastPoll(){
		$sLaatstePoll="
			SELECT
				id, titel, open, uid AS startUID
			FROM
				forum_topic
			WHERE
				soort='T_POLL'
			ORDER BY
				datumtijd DESC
			LIMIT 1;";
		$rLaatstePoll=$this->_db->query($sLaatstePoll);
		echo mysql_error();
		if($this->_db->numRows($rLaatstePoll)==1){
			$aLaatstePoll=$this->_db->next($rLaatstePoll);
			$iTopicID=$aLaatstePoll['id'];
			//poll opties ophaelen
			$sOptiesQuery="
				SELECT
					id, optie, stemmen
				FROM
					forum_poll
				WHERE
					topicID=".$iTopicID."
				ORDER BY
					id;";
			$rOptiesResult=$this->_db->query($sOptiesQuery);
			if($this->_db->numRows($rOptiesResult)!=0){
				//zoo, opties in een array rossen
				while($aOptieData=$this->_db->next($rOptiesResult)){
					$aOpties[]=array(
						'id' => $aOptieData['id'],
						'topicID' => $iTopicID,
						'titel' => $aLaatstePoll['titel'],
						'open' => $aLaatstePoll['open'],
						'startUID' => $aLaatstePoll['startUID'],
						'optie' => $aOptieData['optie'],
						'stemmen' => $aOptieData['stemmen'] );
				}
				return $aOpties;
			}else{
				return false;
			}
		}else{
			return false;
		}	
	}
	function getPollStemmen(){
		$sStemmenQuery="
			SELECT
				sum(stemmen) as totaal
			FROM
				forum_poll
			WHERE
				topicID=".$this->_forum->getID()."
			LIMIT 1;";
		$rStemmenResult=$this->_db->query($sStemmenQuery);
		if($this->_db->numRows($rStemmenResult)==1){
			$aStemmen=$this->_db->next($rStemmenResult);
			return $aStemmen['totaal'];
		}else{
			return false;
		}
	}
	function topicIDvoorOptie($iOptieID){
		$iOptieID=(int)$iOptieID;
		$sOptieQuery="
			SELECT
				topicID
			FROM
				forum_poll
			WHERE
				id=".$iOptieID."
			LIMIT 1;";
		$rOptieResult=$this->_db->query($sOptieQuery);
		if($this->_db->numRows($rOptieResult)==1){
			$aOptie=$this->_db->next($rOptieResult);
			return $aOptie['topicID'];
		}else{
			return false;
		}
	}
	function topicHeeftPoll(){
		$sHeeftPoll="
			SELECT
				soort
			FROM
				forum_topic
			WHERE
				id=".$this->_forum->getID()."
			LIMIT 1;";
		$rHeeftPoll=$this->_db->query($sHeeftPoll);
		if($this->_db->numRows($rHeeftPoll)==1){
			$aHeeftPoll=$this->_db->next($rHeeftPoll);
			return $aHeeftPoll['soort']=='T_POLL';
		}else{
			return false;
		}
	}
	//controleer of gebruiker al een stem heeft uitgebracht.
	function uidMagStemmen(){
		$sMagStemmen="
			SELECT
				uid
			FROM
				forum_poll_stemmen
			WHERE 
				topicID=".$this->_forum->getID()." AND
				uid='".$this->_lid->getUid()."'
			LIMIT 1;";
		$rMagStemmen=$this->_db->query($sMagStemmen);
		return $this->_db->numRows($rMagStemmen)!=1;
		
	}
	function addStem($iOptieID){
		$iOptieID=(int)$iOptieID;
		$iTopicID=$this->topicIDvoorOptie($iOptieID);
		$uid=$this->_lid->getUid();
		$sAddStem="
			INSERT INTO
				forum_poll_stemmen
			(	
				topicID, optieID, uid
			) VALUES (
				".$iTopicID.", ".$iOptieID.", '".$uid."'
			);";
		$sUpdateTelveld="
			UPDATE
				forum_poll
			SET
				stemmen=stemmen+1
			WHERE
				id=".$iOptieID.";";
		return $this->_db->query($sAddStem) AND $this->_db->query($sUpdateTelveld);
	}
	function validatePollForm(&$sError){
		$bValid=true;
		$sError='';
		if(isset($_POST['titel']) AND isset($_POST['bericht']) AND isset($_POST['opties'])){
			if(strlen($_POST['titel']) < 4){
				$bValid=false;
				$sError.="Het veld 'vraag/stelling' moet minsten 4 tekens bevatten.<br />";
			}
			if(strlen($_POST['bericht']) < 15 ){
				$bValid=false;
				$sError.="Het veld 'bericht' moet minsten 15 tekens bevatten.<br />";
			}
			if(isset($_POST['opties'])){
				foreach($_POST['opties'] as $sOptie){
					if(trim($sOptie)!=''){ $aOpties[]=$sOptie; }
				}
				if(count($aOpties)<2){
					$bValid=false;
					$sError.="U moet minstens twee opties opgeven!";
				}
			}else{
				$bValid=false;
				$sError.='U moet minstens twee opties opgeven!';
			}
			
		}else{
			$bValid=false;
			$sError.='Forumulier is niet compleet';
		}
		return $bValid;
	}
	function maakTopicPoll($iTopicID, $aPostOpties){
		foreach($aPostOpties as $sOptie){
			if(trim($sOptie)!=''){
				$aQuerys[]="
					INSERT INTO 
						forum_poll 
					(
						topicID, optie
					) VALUES (
						".$iTopicID.", '".addslashes($sOptie)."'
					);";
			}
		}//einde foreach optie
		$aQuerys[]="
			UPDATE
				forum_topic
			SET
				soort='T_POLL'
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		//query's doorlopen
		$bOk=true;
		foreach($aQuerys as $sQuery){
			if(!$this->_db->query($sQuery)){
				$bOk=false;
			}
		}
		return $bOk;
	}
	function isStatisticus(){
		return STATISTICUS==$this->_lid->getUid();
	}
	function magPeilingMaken(){
		return $this->isStatisticus() OR $this->_lid->hasPermission('P_FORUM_MOD');
	}
	function peilingVan($uid){
		//STATISTICUS is het uid van de verenigingsstatisticus en staat in include.config.php
		if($uid==STATISTICUS){
			return 'am. Verenigingsstatisticus';
		}else{
			return $this->_lid->getNaamLink($uid, 'civitas', false, false);
		}
	}
}//einde classe
?>
