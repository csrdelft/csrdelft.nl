<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# toevoegen.php
# -------------------------------------------------------------------
# Verwerkt het toevoegen van berichten en ondewerpen in het forum.
# Het formulier bevat: (bericht en topic of title)
# -------------------------------------------------------------------

require_once 'configuratie.include.php';
require_once 'forum/forumonderwerp.class.php';


//als er geen bericht is gaan we sowieso niets doen.
if(!isset($_POST['bericht'])){
	header('location: '.CSR_ROOT.'forum/');
	setMelding('Helaas, er gaat iets goed mis. Er niet eens een bericht (forum/toevoegen.php).', -1);
	exit;
}

if(isset($_POST['email'])){
	if(email_like($_POST['email'])){
		$email=mb_htmlentities($_POST['email']);
	}else{
		header('location: '.CSR_ROOT.'communicatie/forum/');
		setMelding('U moet een geldig email-adres opgeven.', -1);
		exit;
	}
}

//een nieuw topic toevoegen?
if(!isset($_GET['topic']) AND isset($_GET['forum'])){
	$forumonderwerp=new ForumOnderwerp(0);
	$forumonderwerp->setCategorie((int)$_GET['forum']);

	if(strlen(trim($_POST['titel']))<1 OR strlen(trim($_POST['bericht']))<1){
		header('location: '.CSR_ROOT.'communicatie/forum/categorie/'.$forumonderwerp->getCategorieID());
		setMelding('De titel of het bericht kunnen niet leeg zijn (forum/toevoegen.php).', -1);
		exit;
	}
	if(!$forumonderwerp->magToevoegen()){
		header('location: '.CSR_ROOT.'communicatie/forum/categorie/'.$forumonderwerp->getCategorieID());
		setMelding('U heeft niet voldoende rechten om onderwerpen toe te voegen (ForumOnderwerp::magToevoegen(); forum/toevoegen.php).', -1);
		exit;
	}
	if($forumonderwerp->needsModeration()){
		if(!isset($email)){
			setMelding('Email-adres opgeven is verplicht!', -1);
			header('location: '.CSR_ROOT.'communicatie/forum/');
			exit;
		}
		//spam detection. if hidden field 'firstname' is not empty, fail.
		if(isset($_POST['firstname']) && $_POST['firstname']!=''){
			header('location: '.CSR_ROOT.'communicatie/forum/');
			exit;
		}
	}

	//addTopic laadt zelf de boel in die hij net heeft toegevoegd...
	if($forumonderwerp->add($_POST['titel'])===false){
		header('location: '.CSR_ROOT.'communicatie/forum/');
		setMelding('Helaas, er gaat iets goed mis bij het toevoegen van het onderwerp (ForumOnderwerp::add(); forum/toevoegen.php)', -1);
		exit;
	}
}else{
	if($_GET['topic']==(int)$_GET['topic']){
		//niets nieuws toevoegen, het opgegeven onderwerp gebruiken.
		$forumonderwerp=new ForumOnderwerp((int)$_GET['topic']);
	}else{
		//kennelijk een brak topicID, dan maar weer terug naar het phorum...
		header('location: '.CSR_ROOT.'communicatie/forum/');
		setMelding('Helaas, er moet wel een correct onderwerp-nummer opgegeven worden.', -1);
		exit;
	}
}

if($forumonderwerp->magToevoegen()){
	if(strlen(trim($_POST['bericht']))>0){
		$bericht=$_POST['bericht'];
		if($forumonderwerp->needsModeration()){
			if(!isset($email)){
				header('location: '.CSR_ROOT.'communicatie/forum/');
				setMelding('Email-adres opgeven is verplicht!', -1);
				exit;
			}

			//spam detection. if hidden field 'firstname' is not empty, fail.
			if(isset($_POST['firstname']) AND $_POST['firstname']!=''){
				header('location: '.CSR_ROOT.'communicatie/forum/');
				exit;
			}
		}else{
			$email='';
		}

		if($forumonderwerp->addPost($bericht,$email)!==false){
			if(isset($_SESSION['compose_snapshot'])){
				$_SESSION['compose_snapshot']=null;
			}
			if($forumonderwerp->needsModeration()){
				header('location: '.CSR_ROOT.'communicatie/forum/categorie/'.$forumonderwerp->getCategorieID());
				setMelding('Uw bericht is verwerkt, het zal binnenkort goedgekeurd worden.', 1);
				exit;
			}
		}else{
			setMelding('Helaas ging er iets mis met het toevoegen van het bericht (forumOnderwerp::addPost()).', -1);
		}
	}else{
		setMelding('Uw bericht is leeg, lege berichten worden niet geaccepteerd.', -1);
	}
}else{
	setMelding('Hela, volgens mij mag u dit niet... (forumOnderwerp::magToevoegen())', -1);
}
header('location: '.CSR_ROOT.'communicatie/forum/onderwerp/'.$forumonderwerp->getID().'/'.$forumonderwerp->getPaginaCount().'#laatste');

?>
