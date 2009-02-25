<?php
# instellingen & rommeltjes
require_once('include.config.php');


if(isset($_GET['topic'])){
	$iTopicID=(int)$_GET['topic'];
	require_once('forum/class.forumonderwerp.php');
	$forum = new ForumOnderwerp();
	$forum->load($iTopicID);
	require_once('forum/class.forumpoll.php');
	$poll = new ForumPoll($forum);
	$iCatID=$forum->getCatID();
	//kijken of er voldoende rechten zijn voor het stemmen op een peiling.
	if($forum->catExistsVoorUser($iCatID) AND $lid->hasPermission($forum->getRechten_post($iCatID))) {
		//controleer of er een polloptie is meegegeven en of het onderwerp wel een poll heeft.
		if(isset($_POST['pollOptie']) AND $poll->topicHeeftPoll()){
			$iPollOptie=(int)$_POST['pollOptie'];
			//controleren of er al gestemd is door deze gebruiker
			if($poll->uidMagStemmen()){
				//stemmen dan maar...
				if($poll->addStem($iPollOptie)){
					if($iTopicID==7){
						header('location: '.CSR_ROOT.'leden/');
						exit;
					}
				}else{
						$_SESSION['melding']='Optie bestaat niet.';
				}
			}else{
				$_SESSION['melding']='U mag maar een keer stemmen.';
			}
		}else{
			$_SESSION['melding']='Onjuiste gegevens.';
		}
	}else{
		$_SESSION['melding']='U mag hier niet stemmen.';
	}
	header('location: '.CSR_ROOT.'communicatie/forum/onderwerp/'.$iTopicID);
}else{
	header('location: '.CSR_ROOT.'forum/');
	$_SESSION['melding']='Hier snap ik geen snars van (waar is het topicID?).';
}
?>
