<?php

# prevent global namespace poisoning
main();
exit;
function main() {

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	session_start();
	$db = new MySQL();
	$lid = new Lid($db);

	### Pagina-onderdelen ###

	# menu's
	require_once('class.dbmenu.php');
	$homemenu = new DBMenu('home', $lid, $db);
	$infomenu = new DBMenu('info', $lid, $db);
	if ($lid->hasPermission('P_LOGGED_IN')) $ledenmenu = new DBMenu('leden', $lid, $db);

	require_once('class.simplehtml.php');
	require_once('class.hok.php');
	$homemenuhok = new Hok($homemenu->getMenuTitel(), $homemenu);
	$infomenuhok = new Hok($infomenu->getMenuTitel(), $infomenu);
	if ($lid->isLoggedIn()) $ledenmenuhok = new Hok($ledenmenu->getMenuTitel(), $ledenmenu);

	require_once('class.loginform.php');
	$loginform = new LoginForm($lid);
	$loginhok = new Hok('Ledenlogin', $loginform);

	# Datum
	require_once('class.includer.php');
	$datum = new Includer('', 'datum.php');
	
	//inhoud
	require_once('class.forum.php');
	$forum = new Forum($lid, $db);
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
					header('location: http://csrdelft.nl/forum/onderwerp/'.$iTopicID.'#'.$iPostID);
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
			header('location: http://csrdelft.nl/forum/?fout='.base64_encode('U mag dit bericht niet bewerken.'));
			exit;
		}
	}else{
		# geen rechten
		require_once('class.includer.php');
		$midden = new Includer('', 'geentoegang.html');
	}	

	### Kolommen vullen ###
	require_once('class.column.php');
	$col0 = new Column(COLUMN_MENU);
	$col0->addObject($homemenuhok);
	$col0->addObject($infomenuhok);
	if ($lid->isLoggedIn()) $col0->addObject($ledenmenuhok);
	$col0->addObject($loginhok);
	$col0->addObject($datum);

	$col1 = new Column(COLUMN_MIDDENRECHTS);
	$col1->addObject($midden);

	# Pagina maken met deze twee kolommen
	require_once('class.page.php');
	$page = new Page();
	$page->addColumn($col0);
	$page->addColumn($col1);

	$page->view();
	
}

?>
