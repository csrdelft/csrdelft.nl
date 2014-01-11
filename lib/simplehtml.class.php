<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# simplehtml.class.php
# -------------------------------------------------------------------
# Van deze klasse worden alle klassen afgeleid die ervoor
# bedoeld zijn om uiteindelijk HTML te tonen met view()
# -------------------------------------------------------------------
abstract class SimpleHTML {

	/**
	 * Genereer html
	 */
	public function view() {}
	
	public function getTitel() { return 'C.S.R. Delft'; }
	
	/**
	 * Geeft berichten weer die opgeslagen zijn in de sessie met met setMelding($message, $lvl=0)
     * Levels can be:
	 *
	 * -1 error
	 *  0 info
	 *  1 success
	 *  2 notify
	 *
	 * @return string html van melding(en) of lege string
	 */
	public function getMelding(){
		if(isset($_SESSION['melding']) AND is_array($_SESSION['melding'])){
			$sMelding='<div id="melding">';
			$shown=array();
			foreach($_SESSION['melding'] as $msg){
				$hash = md5($msg['msg']);
				//if(isset($shown[$hash])) continue; // skip double messages
				$sMelding.='<div class="msg'.$msg['lvl'].'">';
				$sMelding.=$msg['msg'];
				$sMelding.='</div>';
				$shown[$hash] = 1;
			}
			$sMelding.='</div>';
			//maar één keer tonen, de melding.
			unset($_SESSION['melding']);
			return $sMelding;
		}else{
			return '';
		}
	}
	public function setMelding($sMelding, $level=-1){
		setMelding($sMelding, $level);
	}
	
	public static function invokeRefresh($url=null, $melding='', $level=-1){
		//als $melding een array is die uit elkaar halen
		if(is_array($melding)){
			list($melding, $level)=$melding;
		}
		if($melding!=''){
			setMelding($melding, $level);
		}
		if($url==null){
			$url=CSR_ROOT.$_SERVER['REQUEST_URI'];
		}
		header('location: '.$url);
		exit;
	}
	
	public static function getStandaardZijkolom() {
		$zijkolom = array();
		// Is het al...
		if (Instelling::get('zijbalk_ishetal') != 'niet weergeven') {
			require_once('ishetalcontent.class.php');
			$zijkolom[] = new IsHetAlContent(Instelling::get('zijbalk_ishetal'));
		}
		// Ga snel naar
		if (Instelling::get('zijbalk_gasnelnaar') == 'ja') {
			require_once('menu/MenuView.class.php');
			$zijkolom[] = new MenuView('gasnelnaar', 3);
		}
		// Agenda
		if (LoginLid::instance()->hasPermission('P_AGENDA_READ') && Instelling::get('zijbalk_agendaweken') > 0) {
			require_once('agenda/agenda.class.php');
			require_once('agenda/agendacontent.class.php');
			$zijkolom[] = new AgendaZijbalkContent(new Agenda(), Instelling::get('zijbalk_agendaweken'));
		}
		// Laatste mededelingen
		if (Instelling::get('zijbalk_mededelingen') > 0) {
			require_once('mededelingen/mededeling.class.php');
			require_once('mededelingen/mededelingencontent.class.php');
			$zijkolom[] = new MededelingenZijbalkContent(Instelling::get('zijbalk_mededelingen'));
		}
		// Nieuwste belangrijke forumberichten
		if (Instelling::get('zijbalk_forum_belangrijk') >= 0) {
			require_once 'forum/forumcontent.class.php';
			$zijkolom[] = new ForumContent('lastposts_belangrijk');
		}
		// Nieuwste forumberichten
		if (Instelling::get('zijbalk_forum') > 0) {
			require_once 'forum/forumcontent.class.php';
			$zijkolom[] = new ForumContent('lastposts');
		}
		// Zelfgeposte forumberichten
		if (Instelling::get('zijbalk_forum_zelf') > 0) {
			require_once 'forum/forumcontent.class.php';
			$zijkolom[] = new ForumContent('lastposts_zelf');
		}
		// Nieuwste fotoalbum
		if (Instelling::get('zijbalk_fotoalbum') == 'ja') {
			require_once 'fotoalbumcontent.class.php';
			$zijkolom[] = new FotalbumZijbalkContent();
		}
		// Komende verjaardagen
		if (Instelling::get('zijbalk_verjaardagen') > 0) {
			require_once 'lid/verjaardagcontent.class.php';
			$zijkolom[] = new VerjaardagContent('komende');
		}
		return $zijkolom;
	}
	
	public static function getDebug($sql = true, $get = true, $post = true, $files = false, $session = true, $cookie = true) {
		$debug = '';
		if ($sql) {
			$debug .= '<hr />SQL<hr />';
			$debug .= '<pre>' . htmlentities(print_r(array("PDO" => Database::instance()->getQueries(), "MySql" => MySql::instance()->getQueries()), true)) . '</pre>';
		}
		if ($get) {
			$debug .= '<hr />GET<hr />';
			if (count($_GET) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_GET, true)) . '</pre>';
			}
		}
		if ($post) {
			$debug .= '<hr />POST<hr />';
			if (count($_POST) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_POST, true)) . '</pre>';
			}
		}
		if ($files) {
			$debug .= '<hr />FILES<hr />';
			if (count($_FILES) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_FILES, true)) . '</pre>';
			}
		}
		if (isset($_GET['debug_session'])) { // only print session if relevent, because it might be quite big.
			$debug .= '<hr />SESSION<hr />';
			if (count($_SESSION) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_SESSION, true)) . '</pre>';
			}
		}
		if ($cookie) {
			$debug .= '<hr />COOKIE<hr />';
			if (count($_COOKIE) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_COOKIE, true)) . '</pre>';
			}
		}
		return $debug;
	}
}
