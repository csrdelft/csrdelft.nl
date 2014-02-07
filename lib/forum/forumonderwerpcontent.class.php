<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumonderwerpcontent.php
# -------------------------------------------------------------------

class ForumOnderwerpContent extends TemplateView {

	private $forumonderwerp;
	//nul als er niets geciteerd wordt, anders een postID
	private $citeerPost = 0;
	private $titel = 'forum';
	private $error = false;

	public function __construct($bForumonderwerp) {
		parent::__construct();
		$this->forumonderwerp = $bForumonderwerp;
	}

	public function citeer($iPostID) {
		$this->citeerPost = (int) $iPostID;
	}

	private function getCiteerPost() {
		return $this->citeerPost;
	}

	function getTitel() {
		$sTitel = 'Forum - ' .
				$this->forumonderwerp->getCategorie()->getNaam() . ' - ' .
				$this->forumonderwerp->getTitel();
		return $sTitel;
	}

	function view() {
		if ($this->forumonderwerp->getPosts() === false) {
			setMelding($this->forumonderwerp->getError());
			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>';
			echo $this->getMelding();
		} else {
			$this->smarty->assign('onderwerp', $this->forumonderwerp);
			//wat komt er in de textarea te staan?
			$textarea = '';
			if ($this->getCiteerPost() != 0) {
				$post = $this->forumonderwerp->getSinglePost($this->getCiteerPost());
				if (is_array($post)) {
					if (!$this->forumonderwerp->isIngelogged()) {
						$aPost['tekst'] = CsrUbb::filterPrive($post['tekst']);
					}
					$textarea = '[citaat=' . $post['uid'] . ']' . htmlspecialchars($post['tekst']) . '[/citaat]';
				}
			} else {
				if (isset($_SESSION['compose_snapshot'])) {
					$textarea = htmlspecialchars($_SESSION['compose_snapshot']);
				}
			}
			$this->smarty->assign('textarea', $textarea);

			$this->smarty->display('forum/onderwerp.tpl');
		}
	}

}

?>
