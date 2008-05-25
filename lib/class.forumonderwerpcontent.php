<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumonderwerpcontent.php
# -------------------------------------------------------------------


require_once('class.simplehtml.php');

class ForumOnderwerpContent extends SimpleHTML {
	var $_forum;
	
	//nul als er niets geciteerd wordt, anders een postID
	var $citeerPost=0;
	
	var $_sTitel='forum';
	
	var $_sError=false;
	
	function ForumOnderwerpContent($bForumonderwerp){
		$this->_forum=$bForumonderwerp;
	}

	
	public function citeer($iPostID){
		//TODO: check of deze post wel bestaat, anders niets citeren.
		$this->citeerPost=(int)$iPostID;
	}
		
	private function getCiteerPost(){
		return $this->citeerPost;
		
	}
	function getTitel(){
		$sTitel='Forum - '.
			$this->_forum->getCatTitel().' - '.
			$this->_forum->getTitel();
		return $sTitel;
	}
	function view(){
		if($this->_forum->getPosts()===false){
			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>';
			echo 'Dit gedeelte van het forum is niet beschikbaar voor u, u zult moeten inloggen, of terug gaan naar <a href="/communicatie/forum/">het forum</a>';
			if($this->_forum->isModerator()){ 
				echo '<h2>Debuginformatie</h2><pre>'.print_r($this, true).'</pre>'; 
			}

		}else{
			$smarty=new Smarty_csr();
			$smarty->assign('forum', $this->_forum);
			$smarty->assign('melding', $this->getMelding());
			if($this->_forum->getSoort()=='T_POLL'){
				require_once('class.forumpoll.php');
				require_once('class.pollcontent.php');
				$peiling=new ForumPoll($this->_forum);
				$peilingContent=new PollContent($peiling);
				$smarty->assign('peiling', $peilingContent);
			}
			
			//eventueel een voorbeeld voor een bericht laten zien.
			if(isset($_POST['bericht'], $_POST['submit']) AND $_POST['submit']=='voorbeeld'){
				$smarty->assign('postvoorbeeld', $_POST['bericht']);
			}
			//wat komt er in de textarea te staan?
			if($this->getCiteerPost()!=0){
				$aPost=$this->_forum->getSinglePost($this->getCiteerPost());
				$textarea='[citaat='.$aPost['uid'].']'.htmlspecialchars($aPost['tekst']).'[/citaat]';
			}elseif(isset($_POST['bericht'])){
				$textarea=htmlspecialchars($_POST['bericht']);
			}else{
				$textarea='';
			}
			$smarty->assign('textarea', $textarea);
			$smarty->assign('citeerPost', $this->getCiteerPost());
				
			$smarty->display('forum/onderwerp.tpl');
		}	
	}
}
?>
