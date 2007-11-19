<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# bewerken.php
# -------------------------------------------------------------------
# Verwerkt het bewerken van berichten in het forum.
# -------------------------------------------------------------------

require_once('include.config.php');

//inhoud
require_once('class.forumonderwerp.php');
$forum = new ForumOnderwerp();
//is er uberhaupt wel een postID welke bewerkt moet worden
if(isset($_GET['post'])){
	$iPostID=(int)$_GET['post'];
	$forum->loadByPostID($iPostID);
	//kijken of gebruiker dit bericht mag bewerken
	if($forum->magBewerken($iPostID)){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			$bericht=$db->escape(trim($_POST['bericht']));
			if($forum->editPost($iPostID, $bericht)){
				$iTopicID=$forum->getTopicVoorPostID($iPostID);
				header('location: '.CSR_ROOT.'forum/onderwerp/'.$iTopicID.'#post'.$iPostID);
				exit;
			}else{
				require_once('class.forumcontent.php');
				$midden = new ForumContent($forum, 'bewerk');
			}
		}else{
			require_once('class.forumcontent.php');
			$midden = new ForumContent($forum, 'bewerk');
		}
	}else{
		header('location: '.CSR_ROOT.'forum/');
		$_SESSION['melding']='U mag dit bericht niet bewerken.';
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
$pagina=new csrdelft($midden);
$pagina->setZijkolom($zijkolom);

$pagina->view();


?>
