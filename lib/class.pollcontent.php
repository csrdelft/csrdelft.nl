<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.pollcontent.php
# -------------------------------------------------------------------
# Historie:
# 08-03-2006 Jieter
# . gemaakt
#
require_once('bbcode/include.bbcode.php');

class PollContent extends SimpleHTML {
	var $_forumPoll;
	
	var $_sError=false;
	
	function PollContent($forumPoll){
		$this->_forumPoll=$forumPoll;
	}
	
	function view(){
		$poll=$this->_forumPoll->getLastPoll();
		$iTopic=$poll[0]['topicID'];
		$iPollStemmen=$this->_forumPoll->getPollStemmen($iTopic);
		//er mag maar één keer per *ingelloged lid* per poll gestemd worden, en alleen als het topic open is.
		//er mag maar één keer per *ingelloged lid* per poll gestemd worden, en alleen als het topic open is.
		$bMagStemmen=$this->_forumPoll->uidMagStemmen($iTopic) AND true;
		$sPeilingVan=mb_htmlentities('Dit is een peiling van '.$this->_forumPoll->peilingVan($poll[0]['startUID']));
		//html dan maer
		echo '<form action="/forum/stem/'.$iTopic.'" method="post" >';
		echo '<table style="width: 100%; margin: 10px 10px 10px 10px; background-color: #f1f1f1;" border="0">';
		//poll vraag nog een keer
		echo '<tr><td colspan="3">';
		if($iTopic==7){
			echo htmlentities($poll[0]['titel'], ENT_COMPAT, 'UTF-8');
		}else{
			echo '<a class="forumGrootlink" href="/forum/onderwerp/'.$iTopic.'">'.htmlentities($poll[0]['titel'], ENT_COMPAT, 'UTF-8').'</a>';
		}
		echo '<br /><em>'.$sPeilingVan.'</em>. Er is '.$iPollStemmen.' keer gestemd.</td></tr>';
		foreach($poll as $aPollOptie){
			//lengte van de balk, en het percentage van de stemmen
			if($aPollOptie['stemmen']!=0){
				$fPercentage=(100/$iPollStemmen)*$aPollOptie['stemmen'];
				$iBalkLengte=floor($fPercentage*2); //($iPollOpties/1.5));
			}else{
				$fPercentage=$iBalkLengte=0;
			}
			echo '<tr><td>';
			//forumulier enkel tonen als er gestemd mag worden
			if($bMagStemmen){
				echo '<input type="radio" name="pollOptie" id="'.$aPollOptie['id'].'" value="'.$aPollOptie['id'].'" />';
			}
			echo '<label for="'.$aPollOptie['id'].'">'.htmlentities($aPollOptie['optie'], ENT_COMPAT, 'UTF-8').'</label></td>';
			echo '<td><img src="/images/frikandel.png" height="20px" width="'.$iBalkLengte.'px" title="een del, lekker!" /></td>';
			echo '<td style="width: 90px">'.round( $fPercentage, 2).'% ('.$aPollOptie['stemmen'].')</td></tr>';
		}
		//verzendknopje enkel tonen als er gestemd mag worden
		if($bMagStemmen){
			echo '<tr><td colspan="3"><input type="submit" value="stemmen" name="stemmen" /> '; 
			echo '<em>(Als u hier klikt wordt uw eventuele commentaar niet opgeslagen)</em></td></tr>';
		}
		if((STATISTICUS==$this->_forumPoll->_forum->_lid->getUid()) OR $this->_forumPoll->_forum->_lid->hasPermission('P_FORUM_MOD')){
			echo '<tr><td colspan="3">';
			if(STATISTICUS==$this->_forumPoll->_forum->_lid->getUid()){
				echo '<br />Dag amice statisticus!';
			}
			echo '<br />U kunt <a href="/forum/maak-stemming/1"><strong>hier peilingen toevoegen</strong></a>. Zodra u een nieuwe
			peiling toegevoegd heeft komt die op deze plaats te staan. Die peiling komt terecht in de categorie <a href="/forum/categorie/1">C.S.R.-zaken</a>
			op het forum. Wilt u dat niet, klik dan <a href="/forum/maak-stemming/1"><strong>hier om een peiling toe te voegen waar <em>niet</em> op gereageerd kan worden.</strong></a></td></tr>';
		}
		echo '</table>';
	}
}
