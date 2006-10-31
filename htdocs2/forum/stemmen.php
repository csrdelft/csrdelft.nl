<?php
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');


if(isset($_GET['topic'])){
	$iTopicID=(int)$_GET['topic'];
	require_once('class.forum.php');
	$forum = new Forum($lid, $db);
	require_once('class.forumpoll.php');
	$poll = new ForumPoll($forum);
	$iCat=$forum->getCategorieVoorTopic($iTopicID);
	//kijken of er voldoende rechten zijn voor het stemmen op een peiling.
	if($forum->catExistsVoorUser($iCat) AND $lid->hasPermission($forum->getRechten_post($iCat))) {
		//controleer of er een polloptie is meegegeven en of het onderwerp wel een poll heeft.
		if(isset($_POST['pollOptie']) AND $poll->topicHeeftPoll($iTopicID)){
			$iPollOptie=(int)$_POST['pollOptie'];
			//controleren of er al gestemd is door deze gebruiker
			if($poll->uidMagStemmen($iTopicID)){
				//stemmen dan maar...
				if($poll->addStem($iPollOptie)){
					if($iTopicID==7){
						header('location: http://csrdelft.nl/leden/');
					}else{
						header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					}	
				}else{
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
					$_SESSION['forum_foutmelding']='Optie bestaat niet.';
				}
			}else{
				header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
				$_SESSION['forum_foutmelding']='U mag maar een keer stemmen.';
			}
		}else{
			header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
			$_SESSION['forum_foutmelding']='Onjuiste gegevens.';
		}
	}else{
		header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID);
		$_SESSION['forum_foutmelding']='U mag hier niet stemmen.';
	}
}else{
	header('location: http://csrdelft.nl/forum/');
	$_SESSION['forum_foutmelding']='Hier snap ik geen snars van (waar is het topicID?).';
}
?>
