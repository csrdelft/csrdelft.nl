<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.kolom.php
# -------------------------------------------------------------------


require_once('class.simplehtml.php');

class string2object{
	private $_string;
	function string2object($string){
		$this->_string=$string;
	}
	function view(){
		echo $this->_string;
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
	private function defaultView(){

			# Ga snel naar
			if(Instelling::get('zijbalk_gasnelnaar')=='ja'){
				require_once('class.menu.php');
				$this->add(new stringincluder(Menu::getGaSnelNaar()));
			}
			
			# Agenda
			require_once('class.pagina.php');
			require_once('class.paginacontent.php');
			$pagina=new Pagina('agendazijbalk');
			$paginacontent=new PaginaContent($pagina);
			$this->add($paginacontent);

			# Laatste mededelingen
			if(Instelling::get('zijbalk_mededelingen')>0){
				require_once('class.nieuwscontent.php');
				require_once('class.nieuws.php');
				$nieuws = new Nieuws();
				$nieuwscontent = new NieuwsContent($nieuws);
				$nieuwscontent->aantal=Instelling::get('zijbalk_mededelingen');
				$nieuwscontent->setActie('laatste');
				$this->add($nieuwscontent);
			}

			# Laatste forumberichten
			if(Instelling::get('zijbalk_forum')>0){
				require_once 'forum/class.forumcontent.php';
				$forumcontent=new ForumContent('lastposts');
				$this->add($forumcontent);
			}
			if(Instelling::get('zijbalk_forum_zelf')>0){
				require_once 'forum/class.forumcontent.php';
				$forumcontent=new ForumContent('lastposts_zelf');
				$this->add($forumcontent);
			}

			# Komende 10 verjaardagen
			if(Instelling::get('zijbalk_verjaardagen')>0){
				require_once 'class.verjaardagcontent.php';
				$this->add(new VerjaardagContent('komende'));
			}
	}
	public function view() {
		# Als er geen balk is laten we de standaard-inhoud zien
		if (count($this->_objects)==0){
			$this->defaultView();
		}

		foreach ($this->_objects as $object) {
			$object->view();
			echo '<br />';
		}
	}
}

?>
