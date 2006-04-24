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

	header('Content-Type: text/xml; charset=UTF-8');
	if ($lid->hasPermission('P_FORUM_READ')) {
		require_once('class.forum.php');
		$forum = new Forum($lid, $db);
		require_once('class.simplehtml.php');
		require_once('class.forumcontent.php');
		$midden = new ForumContent($forum, 'rss');
		
		$midden->view();
	}
}

?>
