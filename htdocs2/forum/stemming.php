<?php
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

$sError='';
# Het middenstuk
if ($lid->hasPermission('P_FORUM_MOD') OR $lid->getUid()==STATISTICUS){
	require_once('class.forum.php');
	$forum = new Forum($lid, $db);
	//gebruik de standaard categorie als de categorie niet bestaat of niet gezet is.
	if(!(isset($_GET['cat']) AND $forum->catExistsVoorUser($_GET['cat']))){
		$iCatID=7;
	}else{
		$iCatID=(int)$_GET['cat'];
	}
	if($_SERVER['REQUEST_METHOD']=='POST'){
		//ff de boel verwerken..
		require_once('class.forumpoll.php');
		$poll = new ForumPoll($forum);
		if($poll->validatePollForm($sError)){
			//bbcode ding doen
			require_once('bbcode/include.bbcode.php');
			$bbcode_uid=bbnewuid();
			$sBericht=bbsave($_POST['bericht'], $bbcode_uid, $db->dbResource());
			
			$iTopicID=$forum->addPost($sBericht, $bbcode_uid, $topic=0, $iCatID, $_POST['titel']);
			if($iTopicID!==false){
				//poll toevoegen aan topic.
				if($poll->maakTopicPoll($iTopicID, $_POST['opties'])){
					//gelukt.
					header('location: /forum/onderwerp/'.$iTopicID);
					$_SESSION['forum_foutmelding']='Peiling is met succes toegevoegd.';
					exit;
				}else{
					//mislukt.
					echo 'maakTopicPoll is mislukt;';
					exit;
				}
			}else{
				//mislukt.
				echo 'maakTopicPoll is mislukt;';
				exit;
			}
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

$page=new csrdelft($midden, $lid, $db);
$page->setZijkolom($zijkolom);
$page->view();
	


?>
