<?php
/**
 * MenuItem.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een menu-item instantie beschrijft een menu onderdeel van een menu-boom en heeft daarom een parent.
 * 
 */
class MenuItem {

	# primary key
	private $menu_id; # int 11
	
	private $parent_id; # int 11
	private $prioriteit; # int 11
	private $tekst; # string 50
	private $link; # string 255
	private $permission; # string 255
	private $zichtbaar; # boolean
	private $menu; # string 255
	
	public $children;
	
	public function __construct($miid=0, $pid=0, $prio=0, $text='', $link='', $perm='', $show=false, $menu='') {
		$this->menu_id = (int) $miid;
		$this->setParentId($pid);
		$this->setPrioriteit($prio);
		$this->setTekst($text);
		$this->setLink($link);
		$this->setPermission($perm);
		$this->setZichtbaar($show);
		$this->setMenu($menu);
	}
	
	public function getMenuId() {
		return (int) $this->menu_id;
	}
	
	public function getParentId() {
		return (int) $this->parent_id;
	}
	public function getPrioriteit() {
		return (int) $this->prioriteit;
	}
	public function getTekst() {
		return $this->tekst;
	}
	public function getLink() {
		return $this->link;
	}
	public function getPermission() {
		return $this->permission;
	}
	public function getIsZichtbaar() {
		return (boolean) $this->zichtbaar;
	}
	public function getMenu() {
		return $this->menu;
	}
	
	public function setParentId($int) {
		$this->parent_id = (int) $int;
	}
	public function setPrioriteit($int) {
		$this->prioriteit = (int) $int;
	}
	public function setTekst($string) {
		if (!is_string($string)) {
			throw new \Exception('Geen string: tekst');
		}
		$this->tekst = $string;
	}
	public function setLink($string) {
		if (!is_string($string)) {
			throw new \Exception('Geen string: link');
		}
		$this->link = $string;
	}
	public function setPermission($string) {
		if (!is_string($string)) {
			throw new \Exception('Geen string: permission');
		}
		$this->permission = $string;
	}
	public function setZichtbaar($boolean) {
		$this->zichtbaar = (boolean) $boolean;
	}
	public function setMenu($string) {
		if (!is_string($string)) {
			throw new \Exception('Geen string: menu');
		}
		$this->menu = $string;
	}
}

?>