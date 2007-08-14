<?php

# instellingen & rommeltjes
require_once('include.config.php');

//inhoud
require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();
//is er uberhaupt wel een postID welke bewerkt moet worden
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	//kijken of gebruiker dit bericht mag bewerken
	if($forum->magBewerken($iPostID)){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			//beetje ubb geklooi
			require_once('bbcode/include.bbcode.php');
			$bbcode_uid=bbnewuid();
			$bericht=bbsave(trim($_POST['bericht']), $bbcode_uid, $db->dbResource());
			if($forum->editPost($iPostID, $bericht, $bbcode_uid)){
				$iTopicID=$forum->getTopicVoorPostID($iPostID);
				header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID.'#post'.$iPostID);
				exit;
			}else{
				//echo mysql_error();
				require_once('class.forumcontent.php');
				$midden = new ForumContent($forum, 'bewerk');
			}
		}else{
			require_once('class.forumcontent.php');
			$midden = new ForumContent($forum, 'bewerk');
		}
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['forum_foutmelding']='U mag dit bericht niet bewerken.';
		exit;
	}
}else{
	# geen rechten
	$midden = new Includer('', 'geentoegang.html');
}	

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();
	$forumcontent=new forumcontent($forum, 'lastposts');
	$zijkolom->add($forumcontent);
	
# pagina weergeven
$pagina=new csrdelft($midden, $lid, $db);
$pagina->setZijkolom($zijkolom);

$pagina->view();


?>
