<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumonderwerpcontent.php
# -------------------------------------------------------------------



class ForumOnderwerpContent extends SimpleHTML {
	private $forumonderwerp;

	//nul als er niets geciteerd wordt, anders een postID
	private $citeerPost=0;

	private $titel='forum';

	private $error=false;

	public function __construct($bForumonderwerp){
		$this->forumonderwerp=$bForumonderwerp;
	}


	public function citeer($iPostID){ 	$this->citeerPost=(int)$iPostID; }
	private function getCiteerPost(){	return $this->citeerPost; }

	function getTitel(){
		$sTitel='Forum - '.
			$this->forumonderwerp->getCategorie()->getNaam().' - '.
			$this->forumonderwerp->getTitel();
		return $sTitel;
	}
	function view(){
		if($this->forumonderwerp->getPosts()===false){
			$this->setMelding($this->forumonderwerp->getError());

			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>';
			echo $this->getMelding();

		}else{
			$smarty=new Smarty_csr();
			$smarty->assign('onderwerp', $this->forumonderwerp);

			$smarty->assign('melding', $this->getMelding());

			//wat komt er in de textarea te staan?
			$textarea='';
			if($this->getCiteerPost()!=0){
				$post=$this->forumonderwerp->getSinglePost($this->getCiteerPost());
				if(is_array($post)){
					if(!$this->forumonderwerp->isIngelogged()){
						$aPost['tekst']=CsrUBB::filterPrive($post['tekst']);
					}
					$textarea='[citaat='.$post['uid'].']'.htmlspecialchars($post['tekst']).'[/citaat]';
				}
			}else{
				if(isset($_SESSION['compose_snapshot'])){
					$textarea=htmlspecialchars($_SESSION['compose_snapshot']);
				}
			}
			$smarty->assign('textarea', $textarea);

			$smarty->display('forum/onderwerp.tpl');
		}
	}
}
?>
