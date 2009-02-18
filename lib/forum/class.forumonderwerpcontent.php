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
			echo '<pre>'.$this->_forum->getError().'</pre>';
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

			//wat komt er in de textarea te staan?
			if($this->getCiteerPost()!=0){
				$aPost=$this->_forum->getSinglePost($this->getCiteerPost());

				if(!Lid::instance()->hasPermisson('P_LOGGED_IN')){
					$aPost['tekst']=CsrUBB::filterPrive($aPost['tekst']);
				}
				$textarea='[citaat='.$aPost['uid'].']'.htmlspecialchars($aPost['tekst']).'[/citaat]';
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
