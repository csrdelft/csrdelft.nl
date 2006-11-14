<?php
	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	if ($lid->hasPermission('P_FORUM_MOD')) {
		require_once('class.forum.php');
		$forum = new Forum($lid, $db);
		if(isset($_GET['post'])){
			$iPostID=(int)$_GET['post'];
			$iTopicID=$forum->getTopicVoorPostID($iPostID);
			if($forum->keurGoed($iPostID)){
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				$_SESSION['forum_foutmelding']='Onderwerp of bericht nu voor iedereen zichtbaar.';
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				$_SESSION['forum_foutmelding']='Goedkeuren ging mis.';
			}
		}else{
			header('location: http://csrdelft.nl/forum/');
			$_SESSION['forum_foutmelding']='Geen postID gezet.';
		}
	} else {
		header('location: http://csrdelft.nl/forum/');
		$_SESSION['forum_foutmelding']='U heeft daar niets te zoeken.';
	}		

?>
