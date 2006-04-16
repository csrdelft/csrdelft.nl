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
						header('location: /forum/onderwerp/'.$iTopicID.'');
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
