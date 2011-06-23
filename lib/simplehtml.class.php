<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.simplehtml.php
# -------------------------------------------------------------------
# Van deze klasse worden alle klassen afgeleid die ervoor
# bedoeld zijn om uiteindelijk HTML uit te kotsen
# -------------------------------------------------------------------


abstract class SimpleHTML {

	private $_sMelding='';
	//html voor een pagina uitpoepen.
	public function view() {

	}
	public function getMelding(){
		if(isset($_SESSION['melding']) AND trim($_SESSION['melding'])!=''){
			$sError='<div id="melding">'.trim($_SESSION['melding']).'</div>';
			//maar één keer tonen, de melding.
			unset($_SESSION['melding']);
			return $sError;
		}elseif($this->_sMelding!=''){
			return '<div id="melding">'.$this->_sMelding.'</div>';
		}else{
			return '';
		}
	}
	public function setMelding($sMelding){
		$this->_sMelding.=trim($sMelding);
	}
	public static function invokeRefresh($sMelding, $url=null){
		if($sMelding!=''){
			$_SESSION['melding']=$sMelding;
		}
		if($url==null){
			$url=CSR_ROOT.$_SERVER['REQUEST_URI'];
		}
		header('location: '.$url);
		exit;
	}

	//eventueel titel voor een pagina geven
	function getTitel($sTitle=false){
		if($sTitle===false){
			return 'C.S.R. Delft';
		}else{
			return 'C.S.R. Delft - '.$sTitle;
		}
	}
}

class StringIncluder extends SimpleHTML{
	public $string='lege pagina';
	public $title;
	public function __construct($string, $title=''){
		$this->string=$string;
		$this->title=$title;
	}
	function getTitel(){ return $this->title; }
	function view(){
		echo $this->string;
	}
}
class string2object{
	private $_string;
	function string2object($string){
		$this->_string=$string;
	}
	function view(){
		echo $this->_string;
	}
}
class IsHetAlContent extends SimpleHTML{

	private $ishetal=null;
	private $opties=array('jarig', 'vrijdag', 'donderdag', 'zondag', 'borrel', 'lezing', 'lunch', 'avond');

	private $ja=false; //ja of nee.

	public function __construct($ishetal){
		if($ishetal=='willekeurig'){
			$this->ishetal=$this->opties[array_rand($this->opties)];
		}else{
			$this->ishetal=Instelling::get('zijbalk_ishetal');
		}
		switch($this->ishetal){
			case 'jarig': $this->ja=LoginLid::instance()->getLid()->isJarig(); break;
			case 'lunch': $this->ja=(date('Hi')>'1245' AND date('Hi')<'1345'); break;
			case 'avond': $this->ja=(date('Hi')>'1700'); break;
			case 'vrijdag': $this->ja=(date('w')==5); break;
			case 'donderdag': $this->ja=(date('w')==4); break;
			case 'zondag': $this->ja=(date('w')==0); break;
			case 'borrel':
				require_once 'agenda/agenda.class.php';
				$agenda=new Agenda();
				$vandaag=$agenda->isActiviteitGaande($ishetal);
				if($vandaag instanceof AgendaItem){
					if($ishetal=='borrel'){
						$this->ja=time()>$vandaag->getBeginMoment();
					}else{
						$this->ja=time()>$vandaag->getBeginMoment() AND time()<$vandaag->getEindMoment();
					}
				}
			break;
			case 'studeren':
				if(isset($_COOKIE['studeren'])){
					$this->ja=(time()>($_COOKIE['studeren']+5*60) AND date('w')!=0);
					$tijd=$_COOKIE['studeren'];
				}else{
					$tijd=time();
				}
				setcookie('studeren', $tijd, time()+30*60);
			break;
		}
	}

	public function view(){
		switch($this->ishetal){
			case 'jarig':
				echo '<div id="ishetalvrijdag">Ben ik al jarig?<br />';
			break;
			case 'studeren':
				echo '<div id="ishetalvrijdag">Moet ik alweer studeren?<br />';
			break;
			case 'borrel':
			case 'lezing':
				echo '<div id="ishetalvrijdag">Is er een '.$this->ishetal.'?<br />';
			break;
			default:
				echo '<div id="ishetalvrijdag">Is het al '.$this->ishetal.'?<br />';
			break;
		}

		if($this->ja){
			echo '<div class="ja">JA!</div>';

		}else{
			if($this->ishetal=='jarig'){
				echo '<div class="nee">NOG LANG NIET.</div>';
			}else{
				echo '<div class="nee">NEE.</div>';
			}
		}
		echo '</div><br />';
	}

}

class Kolom extends SimpleHTML {

	# Een object is een van SimpleHTML afgeleid object waarin een
	# stuk pagina zit, wat we er met view() uit kunnen krijgen.
	var $_objects = array();

	public function __construct(){

	}

	public function addObject($object){ $this->_objects[]=$object; }
	public function addTekst($string){ $this->addObject(new string2object($string)); }
	# Alias voor addObject
	public function add($object){ $this->addObject($object); }

	public function getTitel(){
		if(isset($this->_objects[0])){
			return $this->_objects[0]->getTitel();
		}
	}
	public function view() {
		foreach ($this->_objects as $object) {
			$object->view();
			echo '<br />';
		}
	}
}
class DefaultKolom extends Kolom{

	public function __construct(){
		# ishetalvrijdag
		if(Instelling::get('zijbalk_ishetal')!='niet weergeven'){
			$this->add(new IsHetAlContent(Instelling::get('zijbalk_ishetal')));
		}
		# Ga snel naar
		if(Instelling::get('zijbalk_gasnelnaar')=='ja'){
			require_once('menu.class.php');
			$this->add(new stringincluder(Menu::getGaSnelNaar()));
		}
		if(LoginLid::instance()->hasPermission('P_ADMIN')){
			require_once 'fotoalbumcontent.class.php';
			$this->add(new FotalbumZijbalkContent());
		}
		# Agenda
		if(LoginLid::instance()->hasPermission('P_AGENDA_READ')){
			if(Instelling::get('zijbalk_agendaweken')>0){
				require_once('agenda/agenda.class.php');
				require_once('agenda/agendacontent.class.php');
				$agenda=new Agenda();
				$agendacontent=new AgendaZijbalkContent($agenda, Instelling::get('zijbalk_agendaweken'));
				$this->add($agendacontent);
			}
		}

		# Laatste mededelingen
		if(Instelling::get('zijbalk_mededelingen')>0){
			require_once('mededelingen/mededeling.class.php');
			require_once('mededelingen/mededelingencontent.class.php');
			$content=new MededelingenZijbalkContent(Instelling::get('zijbalk_mededelingen'));
			$this->add($content);
		}

		# Laatste forumberichten
		if(Instelling::get('zijbalk_forum')>0){
			require_once 'forum/forumcontent.class.php';
			$forumcontent=new ForumContent('lastposts');
			$this->add($forumcontent);
		}
		if(Instelling::get('zijbalk_forum_zelf')>0){
			require_once 'forum/forumcontent.class.php';
			$forumcontent=new ForumContent('lastposts_zelf');
			$this->add($forumcontent);
		}

		# Komende 10 verjaardagen
		if(Instelling::get('zijbalk_verjaardagen')>0){
			require_once 'lid/verjaardagcontent.class.php';
			$this->add(new VerjaardagContent('komende'));
		}
	}
}


?>
