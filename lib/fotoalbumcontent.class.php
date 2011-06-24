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

	private $rows=2;
	private $bigfirst=false;

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

	public function setRows($rows){
		$this->rows=$rows;
	}
	public function setBigfirst(){
		$this->bigfirst=true;
	}
	public function getHTML(){
		$ret='<div class="ubb_block ubb_fotoalbum">';
		$ret.='<h2>'.$this->album->getBreadcrumb();
		$ret.=' &raquo; '.mb_htmlentities($this->album->getNaam());
		$ret.='</h2>';

		$fotos=$this->album->getFotos();

		$limit=$this->rows*7;

		//afronden op hele rijtjes
		if(count($fotos)<$limit){
			$limit=count($fotos)-count($fotos)%7;
			if($limit<7){
				$limit=7;
			}
		}
		if($this->bigfirst && (count($fotos)<11) || $limit < 11){
			$this->bigfirst=false;
		}

		for($i=0; $i<$limit; $i++){
			$foto=$fotos[$i];
			if($foto instanceof Foto){
				$url=$this->album->getPad();
				if(substr($url, 0, 1)!='/'){
					$url='/'.$url;
				}
				$ret.='<a href="/actueel/fotoalbum'.$url.'#'.$foto->getBestandsnaam().'">';
				$ret.='<img src="'.$foto->getThumbURL().'" alt="'.$foto->getBestandsnaam().'"';
				if($this->bigfirst && $i==0){
					$ret.='style="width: 154px; height: 154px;" ';
					$i=$i+3;
				}
				$ret.=' >';
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
