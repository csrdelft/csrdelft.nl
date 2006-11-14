<?php

	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


	if ($lid->hasPermission('P_FORUM_MOD')) {
		require_once('class.forum.php');
		$forum = new Forum($lid, $db);
		if(isset($_GET['topic'])){
			$iTopicID=(int)$_GET['topic'];
			if(isset($_GET['plakkerig'])){
				if($forum->maakTopicPlakkerig($iTopicID)){
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					exit;
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					$_SESSION['forum_foutmelding']='Oeps, feutje, niet gelukt dus';
					exit;
				}
			}elseif(isset($_GET['niet-plakkerig'])){
				if($forum->unmaakTopicPlakkerig($iTopicID)){
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					exit;
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					$_SESSION['forum_foutmelding']='Oeps, feutje, niet gelukt dus';
					exit;
				}
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				$_SESSION['forum_foutmelding']='Hier snap ik geen snars van, niet zooien a.u.b.';
				exit;
			}
		}else{
			header('location: http://csrdelft.nl/forum/');
			$_SESSION['forum_foutmelding']='Niets om te sluiten of te openen.';
			exit;
		}
	} else {
		header('location: http://csrdelft/forum/');
			$_SESSION['forum_foutmelding']='Geen rechten hiervoor';
	}	

?>
