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
		echo '<a href="/actueel/fotoalbum/'.$this->album->getPad().'">';
		echo $this->album->getNaam();
		$limit=6;
		$fotos=$this->album->getFotos();
		for($i=0; $i<$limit; $i++){
			$foto=$fotos[$i];
			if($foto instanceof Foto){
				echo '<img src="'.$foto->getThumbURL().'">';
			}
		}
		echo '</a>';
		echo '</div>';
		echo '</div>';
	}
}
class FotoalbumUbbContent extends SimpleHTML{

	private $rows=2;		//number of rows
	private $per_row=7;		//images per row

	private $big=array();	//array with index of the ones to enlarge

	private $picsize=75; 	//size of an image
	private $rowmargin=2;	//margin between the images

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
	//one integer index or array of integer indexes of images to enlarge
	public function setBig($index){
		if(count(explode(',', $index))>1){
			//explode on ',' and convert tot int.
			$this->big=array_map('intval', explode(',', $index));
		}else{
			$this->big=array((int)$index);
		}
	}

	/*
	 * Build a grid with Foto-objects.
	 *
	 * The index is saved together with the object for correct reference
	 * in case the image is moved one left or one up in the grid at borders
	 */
	private function getGrid(){
		$fotos=$this->album->getFotos();

		//constant for used places
		define('USED', 'USED');

		$grid=array_fill(0, $this->rows, array_fill(0, $this->per_row, null));

		//put big images on grid.
		if(count($this->big)>0){
			foreach($this->big as $bigindex){

				$row=floor($bigindex/$this->per_row);
				$col=($bigindex%$this->per_row);

				//prevent wraparound
				if($col+1>=$this->per_row){ $col=$this->per_row-2; }
				if($row+1>=$this->rows){	$row=$this->rows-2; }

				//if valid image, put on grid.
				if(isset($fotos[$bigindex]) && $fotos[$bigindex] instanceof Foto){
					//if place already USED, do not put photo in.
					if($grid[$row][$col]!=null){ continue; }
					$grid[$row][$col]=array(
						'index' => $bigindex,
						'foto' => $fotos[$bigindex]
					);

					//mark the three places overlapped by this image as used.
					$grid[$row+1][$col]=$grid[$row][$col+1]=$grid[$row+1][$col+1]=USED;
				}
			}
		}
		//put small images on grid.
		$row=$col=0;
		foreach($fotos as $key => $foto){

			//Do not put big pictures on grid now.
			if(in_array($key, $this->big)){ continue; }

			//find first free place.
			while($grid[$row][$col]!=null){
				$col=$col+1;

				//move to next row if end of row is reached.
				if($col>=$this->per_row){
					$row=$row+1;
					$col=$col%$this->per_row;

					//break out of two loops if reached row limit.
					if($row>=$this->rows){ break 2; }
				}
			}
			$grid[$row][$col]=array(
				'index' => $key,
				'foto' => $foto
			);
		}

		//check length of last row and remove it if not full and no big images overlap it.
		if(!in_array(USED, end($grid)) && count(array_filter(end($grid)))<$this->per_row){
			unset($grid[$this->rows-1]);
		}
		if(count(array_filter(end($grid)))==0){
			unset($grid[count($grid)-1]);
		}
		return $grid;
	}

	public function getHTML(){
		$grid=$this->getGrid();
		$delta=$this->picsize+(2*$this->rowmargin);
		$ret='<div class="images" style="height: '.(count($grid)*$delta).'px">';

		foreach($grid as $row => $rowcontents){
			foreach($rowcontents as $col => $foto){
				if(is_array($foto) ){
					$url=$this->album->getPad();

					$ret.='<a href="/actueel/fotoalbum'.$url.'#'.$foto['foto']->getBestandsnaam().'"';
					$ret.=in_array($foto['index'], $this->big) ? 'class="big"' : 'class="sml"';

					$ret.='style=" left: '.($delta*$col).'px; top: '.($delta*$row).'px;">';

					$ret.='<img src="'.$foto['foto']->getThumbURL().'" alt="'.$foto['foto']->getBestandsnaam().'" >';
					$ret.='</a>'."\n";
				}
			}
		}
		$ret.='</div>';

		return
			'<div class="ubb_block ubb_fotoalbum">
				<h2>'.$this->album->getBreadcrumb().' &raquo; '.mb_htmlentities($this->album->getNaam()).'</h2>
				'.$ret.'
			</div>';
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
