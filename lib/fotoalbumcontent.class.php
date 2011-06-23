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
		echo '<a href="/actueel/fotoalbum/'.$this->album->getPad().'" style="text-decoration: none;">'.$this->album->getNaam();
		$limit=6;
		$fotos=$this->album->getFotos();
		for($i=0; $i<$limit; $i++){
			$foto=$fotos[$i];
			echo '<img src="'.$foto->getThumbURL().'" style="float:left; width: 50px; height: 50px; margin: 1px 2px;">';

		}
		echo '</a>';
		echo '</div>';
		echo '</div>';
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
