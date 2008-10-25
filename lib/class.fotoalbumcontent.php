<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.fotoalbumcontent.php
# -------------------------------------------------------------------
# Contentklasse voor het fotoalbum
# -------------------------------------------------------------------


require_once 'class.simplehtml.php';
require_once 'class.fotoalbum.php';

class FotoalbumContent extends SimpleHTML{
	
	private $_fotoalbum;
	
	private $actie;
	
	function FotoalbumContent($fotoalbum){
		$this->_fotoalbum=$fotoalbum;
	}
	
	function getTitel(){
		return 'Fotoalbum';
	}
	
	function setActie($actie){
		$this->actie=$actie;
	}
		
	function view(){
		switch($this->actie){
			case 'album':
				$smarty=new Smarty_csr();
				$smarty->assign('albums',$this->_fotoalbum->getSubalbums());
				$smarty->assign('fotos',$this->_fotoalbum->getFotos());				
				$smarty->display('fotoalbum/album.tpl');
				break;
				
			case 'foto':
								
				break;
		}
	}
}