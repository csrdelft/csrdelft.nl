<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# htdocs/index.php
# -------------------------------------------------------------------
# instellingen & rommeltjes
require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');

# de pagina-inhoud;
require_once('class.includer.php');
$body = new Includer('', 'bestuur.html');

$zijkolom=new kolom();

class besturen{
	function view(){
		echo '<strong>Besturen der C.S.R.:</strong>
		<a href="/vereniging/bestuur.php">2006-2007 De Vries</a><br />
		2005-2006 Neven<br />
		2004-2005 De Jong<br />
		2003-2004 Visser<br />
		2002-2003 Van d. Griendt<br />
		2001-2002 Oosterom<br />
		2000-2001 Bouta<br /><br />
		1999-2000 Jochemse<br />
		1998-1999 Kardol<br />
		1997-1998 Terwel<br />
		1996-1997 Zielhuis<br />
		1995-1996 Heule<br />
		';
	}
}
$zijkolom->addObject(new besturen());
# pagina weergeven
$pagina=new csrdelft($body, $lid, $db);

$pagina->setZijkolom($zijkolom);
$pagina->view();

?>
