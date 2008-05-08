<?php
require_once('include.config.php');

$sError='';
# Het middenstuk
if ($lid->hasPermission('P_FORUM_MOD') OR $lid->getUid()==STATISTICUS){
	require_once('class.forumonderwerp.php');
	$forum = new ForumOnderwerp();
	//gebruik de standaard categorie als de categorie niet bestaat of niet gezet is.
	if(!(isset($_GET['cat']) AND $forum->catExistsVoorUser($_GET['cat']))){
		$iCatID=7;
	}else{
		$iCatID=(int)$_GET['cat'];
	}
	$forum->setCat($iCatID);
	if($_SERVER['REQUEST_METHOD']=='POST'){
		//ff de boel verwerken..
		require_once('class.forumpoll.php');
		$poll = new ForumPoll($forum);
		if($poll->validatePollForm($sError)){
			$forum->addTopic($_POST['titel']);
			
			$iPostID=$forum->addPost($sBericht);
			if($iPostID!==false){
				//poll toevoegen aan topic.
				if($poll->maakTopicPoll($forum->getID(), $_POST['opties'])){
					//gelukt.
					header('location: '.CSR_ROOT.'forum/onderwerp/'.$forum->getID());
					$_SESSION['melding']='Peiling is met succes toegevoegd.';
				}else{
					echo 'maakTopicPoll is mislukt;';
				}
			}else{
				echo 'maakTopicPoll is mislukt;';
			}
			exit;
		}else{
			//formulier maeken
			require_once('class.forumcontent.php');
			$midden= new ForumContent($forum, 'nieuw-poll');
			$midden->setError($sError);
		}
	}else{
		//formulier maeken
		require_once('class.forumcontent.php');
		$midden= new ForumContent($forum, 'nieuw-poll');
	}
} else {
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijkolom in elkaar jetzen
$zijkolom=new kolom();

$page=new csrdelft($midden);
$page->setZijkolom($zijkolom);
$page->addStylesheet('forum.css');
$page->view();
	


?>
