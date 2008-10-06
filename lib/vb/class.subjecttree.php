<?php 

class SubjectTree
{
	var $vb;
	
	function __construct($vb)
	{
		$this->vb = $vb;
	}
	
	function reset() {
		$t = TreeCache::get_TreeCache();
		$t->reset();
	}

	function getTree() {
		$t = TreeCache::get_TreeCache();
		$r = $t->getTree();
		if ($r !== false)
			return $r;
		
		//not cached, calculate and render
		$r = $this->calcTree();
		$t->setTree($r);
		return $r;
	}
	
	private function calcTree() {
		$max = $this->vb->singleSelect("SELECT max(id) AS max FROM vb_subject");
		$max = 1+ $max['max'];
		//some kind of hashmap
		$subs = array_fill(0, $max, NULL);
		
		//objs in map stoppen
		$objs = $this->vb->multipleSelect("SELECT id, parent, name, description FROM vb_subject WHERE status != 'invisible' ORDER BY name ASC");
		for($i = 0; $i < count($objs); $i++)
		{
			$objs[$i]['children'] = array();
			$id = (int) $objs[$i]['id'];
			if (($id > -1 && $id < $max))
			{
				$subs[$id] = &$objs[$i];
			}
		}
		
		//één keer itereren en alles met referenties goed zetten
		for($i = 0; $i < count($objs); $i++)
		{
			$obj = $objs[$i];
			$p = (int) $obj['parent'];
			if (($p != $obj['id']) && ($p >= 0) && ($p < $max) && ($subs[$p]!= NULL))
			{
				$subs[$p]['children'][] = &$objs[$i];
			}
		}
		
		//id 0 is altijd hoofdonderwerp
		return $subs[0];
	}	
	
	function renderTree() {
		$tree = $this->getTree();
		return $this->renderTreeHelper($tree);
	}

	private function renderTreeHelper($tree) {
		$s = "	<a href='index.php?actie=subject&id=".$tree['id']."'
					style = 'border-left: 1px solid #CCCCCC; padding-left:5px'
				>".$tree['name']."</a>
				<div style='padding-left: 50px'>";
		foreach($tree['children'] as $obj)
			$s.= $this->renderTreeHelper($obj);
		$s.="</div>";
		return $s;
	}


	function toOptions($selected,$self = "") {
		$tree = $this->getTree();
		return $this->renderTreeOptions($tree,0,$selected,$self);
	}
	
	private function renderTreeOptions($tree, $indent,$sel,$self) {
		if ($tree['id'] == $self)  //do not render for self, so it cant be selected
			return "";
		$s = "<option value='".$tree['id']."' "
			.($tree['id'] == $sel?"selected='selected'":"").
			"  >".str_repeat('&nbsp;',$indent*3).$tree['name']."</option>";
		foreach($tree['children'] as $obj)
			$s.= $this->renderTreeOptions($obj, $indent +1, $sel,$self);
		return $s;		
	}
	
}


/*
 * TreeCache
 * om niet elke keer de boom opnieuw te hoeven berekenen, grotendeels gejat van Lidcache
 */
class TreeCache{
	//instantie van de huidige classe.
	private static $treeCache;
	
	//memcache-object
	private $memcache;
	private $connected=false;
	
	//als er geen verbinding is in deze array een run-time-only cache bijhouden.
	private $fallbackCache= false;
	
	private function __construct(){
		//eerst even controleren of de Memcache-classe aanwezig is, zoniet gewoon terugvallen naar
		//run-time-only caching.
		if(class_exists('Memcache')){
			$this->memcache=new Memcache;
			$this->connected=@$this->memcache->connect('unix://'.DATA_PATH.'/csrdelft-cache.socket', 0);
		}
		
	}
	
	public static function get_TreeCache(){
		//als er nog geen instantie gemaakt is, die nu maken
		if(!isset(TreeCache::$treeCache)){
			TreeCache::$treeCache = new TreeCache();
		}
		return TreeCache::$treeCache;
	}
	
	public function setTree($tree){
		//alleen de dingen in de cache zetten die we willen gebruiken voor de namen.
		if($this->connected){
			$this->memcache->set("VBSUBJECTTREE", $tree);
		}else{
			$this->fallbackCache=$tree;
		}
	}
	
	public function getTree(){
		if($this->connected){
			return $this->memcache->get("VBSUBJECTTREE");
		}
		return $this->fallbackCache;		
	}
	
	public function reset(){
		if($this->connected){
			$this->memcache->delete("VBSUBJECTTREE");
		}
		$this->fallbackCache = false;
	}
	
}

?>