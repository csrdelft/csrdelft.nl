<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.pollcontent.php
# -------------------------------------------------------------------


require_once('bbcode/include.bbcode.php');

class PollContent extends SimpleHTML {
	var $_forumPoll;
	
	var $_sError=false;
	
	function PollContent($forumPoll){
		$this->_forumPoll=$forumPoll;
	}
	
	function view(){
		$poll=$this->_forumPoll->getPollOpties();
		$iPollStemmen=$this->_forumPoll->getPollStemmen();
		//html dan maer
		echo '<tr/><td class="forumauteur">Er is '.$iPollStemmen.' keer gestemd.</td><td>';
		echo '<form action="/forum/stem/'.$this->_forumPoll->getTopicID().'" method="post" >';
		echo '<table  id="pollTabel">';
		//poll vraag nog een keer
		echo '<tr><td colspan="3"><strong>'.$this->_forumPoll->getPollVraag().'</strong></td></tr>';
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
			if($this->_forumPoll->uidMagStemmen()){
				echo '<input type="radio" name="pollOptie" id="'.$aPollOptie['id'].'" value="'.$aPollOptie['id'].'" />';
			}
			echo '<label for="'.$aPollOptie['id'].'">'.htmlentities($aPollOptie['optie'], ENT_COMPAT, 'UTF-8').'</label></td>';
			echo '<td><img src="'.CSR_PICS.'/forum/frikandel.png" height="20px" width="'.$iBalkLengte.'px" title="een del, lekker!" /></td>';
			echo '<td style="width: 90px">'.round($fPercentage, 2).'% ('.$aPollOptie['stemmen'].')</td></tr>';
		}
		//verzendknopje enkel tonen als er gestemd mag worden
		if($this->_forumPoll->uidMagStemmen()){
			echo '<tr><td colspan="3"><input type="submit" value="stemmen" name="stemmen" /> '; 
			echo '<em>(Als u hier klikt wordt uw eventuele commentaar niet opgeslagen)</em></td></tr>';
		}
		echo '</table></form></td></tr><td class="forumtussenschot" colspan="2"></td></tr>';
	}
}
