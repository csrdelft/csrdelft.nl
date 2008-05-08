<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.kolom.php
# -------------------------------------------------------------------


require_once('class.simplehtml.php');

class string2object{
	private $_string;
	function string2object($string)	{$this->_string=$string;}
	function view()					{echo $this->_string;}
}

class Kolom extends SimpleHTML {

	private $_lid;
	# Een object is een van SimpleHTML afgeleid object waarin een
	# stuk pagina zit, wat we er met view() uit kunnen krijgen.
	var $_objects = array();

	public function Kolom(){
		$this->_lid=Lid::get_lid();
	}
	
	public function addObject(&$object) 	{$this->_objects[] =& $object;}
	public function addTekst($string)		{$this->addObject(new string2object($string));}
	# Alias voor addObject
	public function add(&$object)			{$this->addObject($object);}
	
	public function getTitel(){
		if(isset($this->_objects[0])){
			return $this->_objects[0]->getTitel();
		}
	}
	
	public function view() {
		# Als er geen balk is laten we de standaard-inhoud zien
		if (count($this->_objects)==0){
			# Ga snel naar
			require_once('class.menu.php');
			$this->add(new stringincluder(Menu::getGaSnelNaar()));		
			
			# Laatste mededelingen
			require_once('class.nieuwscontent.php');
			require_once('class.nieuws.php');
			$nieuws = new Nieuws();
			$nieuwscontent = new NieuwsContent($nieuws);
			$nieuwscontent->setActie('laatste');
			$this->add($nieuwscontent);
			
			# Laatste forumberichten
			require_once('class.forum.php'); 
			require_once('class.forumcontent.php');
			$forum=new forum();
			$forumcontent=new forumcontent($forum, 'lastposts');
			$this->add($forumcontent);			
			
			# Komende 10 verjaardagen
			require_once('class.verjaardagcontent.php');		
			$this->add(new VerjaardagContent('komende10'));
		}
		
		foreach ($this->_objects as $object) {
			$object->view();
			echo '<br />';
		}
	}
}

?>
