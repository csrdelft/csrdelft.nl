<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.fotoalbumcontent.php
# -------------------------------------------------------------------
# Contentklasse voor het fotoalbum
# -------------------------------------------------------------------

require_once 'fotoalbum.class.php';

class FotalbumZijbalkContent extends SimpleHtml{

	public function __construct(){
		$this->album=new Fotoalbum('', '');
		$this->album=$this->album->getMostrecentSubAlbum();
	}
	public function view(){

		echo '<div id="zijbalk_fotoalbum">';
		echo '<h1><a href="/actueel/fotoalbum/">Laatste fotoalbum</a></h1>';
		echo '<div class="item">';
		echo '<a href="/actueel/fotoalbum/'.$this->album->getPad().'" style="text-decoration: none;">';
		echo $this->album->getNaam();
		$limit=6;
		$fotos=$this->album->getFotos();
		for($i=0; $i<$limit; $i++){
			$foto=$fotos[$i];
			if($foto instanceof Foto){
				echo '<img src="'.$foto->getThumbURL().'" style="float:left; width: 50px; height: 50px; margin: 1px 2px;">';
			}
		}
		echo '</a>';
		echo '</div>';
		echo '</div>';
	}
}
class FotoalbumUbbContent extends SimpleHTML{

	private $limit=14;

	public function __construct($album=null){
		$this->album=$album;
		if($this->album==null){
			$this->album=new Fotoalbum('', '');
			$this->album=$this->album->getMostrecentSubAlbum();
		}
	}
	public function view(){
		echo $this->getHTML();
	}
	public function getHTML(){
		$ret='<div class="ubb_block ubb_fotoalbum" style="overflow: auto;" >';
		$ret.='<h2>'.$this->album->getBreadcrumb();
		$ret.=' &raquo; '.mb_htmlentities($this->album->getNaam());
		$ret.='</h2>';

		$fotos=$this->album->getFotos();

		//afronden op (bijna) hele rijtjes
		if(count($fotos)<$this->limit && count($fotos)%7 < 6){
			$this->limit=$this->limit-7;
		}

		for($i=0; $i<$this->limit; $i++){
			$foto=$fotos[$i];
			if($foto instanceof Foto){
				$ret.='<a href="/actueel/fotoalbum'.$this->album->getPad().'#'.$foto->getBestandsnaam().'">';
				$ret.='<img src="'.$foto->getThumbURL().'" alt="'.$foto->getBestandsnaam().'" >';
				$ret.='</a>';
			}
		}
		$ret.='</div>';
		return $ret;
	}
}
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
				$smarty->assign('album',$this->_fotoalbum);
				$smarty->display('fotoalbum/album.tpl');
				break;

			case 'foto':

				break;
		}
	}
}
