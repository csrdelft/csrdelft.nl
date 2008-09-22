<?php 

class navigator
{
	var $items = array();
	var $active = -1;
	var $maxitems = 10;
	
	static private $instance;
	
	static function instance()
	{
		if (navigator::$instance == NULL)
		{
			if (isset($_SESSION['navbar']))
				navigator::$instance = unserialize($_SESSION['navbar']);
			else
				navigator::$instance = new navigator();
		}
		return navigator::$instance;
	}
	
	function store()
	{
		$_SESSION['navbar'] = serialize($this);
	}
	
	//zorgt ervoor dat de boom opnieuw gestart wordt (tenzij de nieuwe url al onderdeel uitmaakt van de boom)
	function resetandpush($title)
	{
		$url = $this->getrequest();
		$i = $this->indexof($url);
		if ($i == -1)
			$this->doreset();
		$this->push($title);		
	}
	
	
	private function doreset()
	{
		$this->items = array();
		$this->active= -1;
	}

	function push($title)
	{
		$this->pushUrl($title, $this->getrequest());
	}

	/* voegt een nieuwe navigatie punt toe, of maakt een eerdere opnieuw actief, als als deze al in de items zit */
	function pushUrl($title, $url)
	{
		$i = $this->indexof($url);
		if ($i == -1) //item nog niet eerder bezocht
		{
			//remove alle items na 'active'
			$this->items = array_slice($this->items,0, $this->active+1);
			//append array met nieuwe item
			$this->items[] = array($title, $url);
			$this->active =sizeof($this->items) -1;
		}
		else //item eerder bezocht
		{
			$this->active = $i;
		}
		//cut als te groot
		if (count($this->items) > $this->maxitems)
		{
			$maxindex = $this->active + ($this->maxitems /2);
			//remove alles boven maxindex
			$this->items = array_splice($this->items, $maxindex);
			if(count($this->items) > $this->maxitems) //nog steeds te groot
			{
				$maxindex = count($this->items) - $this->maxitems;
				//remvoe alles voor maxindex
				$this->items = array_splice($this->items, 0, $maxindex);
			}
		}
		//store
		$this->store();

	}
	
	/* geeft de navigate bar weer */
	function show()
	{
		$i = 0;
		$res = "";
		foreach($this->items as $val)
		{
			$res.= ($i!=0?"&gt; ":"")."<a href='".$val[1]."' ".($i++ == $this->active?"class='navbaractive'":"").">".$val[0]."</a>&nbsp;&nbsp;";
		}
		return $res;
	}
	
	/* gaat terug naar het laatst gebruikte item */
	function autoredir($asheader = false)
	{
		if ($asheader)
			echo "location: ".$this->items[$this->active][1];
		else
			echo "<script>window.location='".$this->items[$this->active][1]."'; </script>";
	}
	
	private function indexof($url)
	{
		$i = 0;
		foreach($this->items as $val)
		{
			if ($val[1] == $url)
				return $i;
			$i++;
		}
		return -1;
	}
	
	private function getrequest()
	{
		return substr($_SERVER['REQUEST_URI'],4);
	}
}

?>