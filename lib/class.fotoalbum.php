<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.fotoalbum.php
# -------------------------------------------------------------------
# Dataklassen voor het fotoalbum
# -------------------------------------------------------------------


class Fotoalbum{
	
	private $pad;
	private $mapnaam;
	
	function Fotoalbum($pad,$mapnaam){
		$this->pad=$pad;
		$this->mapnaam=$mapnaam;
	}
	
	function getPad(){
		return $this->pad;
	}
	
	function getMapnaam(){
		return $this->mapnaam;
		/*$positie=strrpos($this->getPad(),'/',1);
		if ($positie===strlen($this->getPad())){
			$positie=0;
		}
		return substr($this->getPad(),$positie);*/
	}
	
	function getNaam(){
		return ucfirst($this->getMapnaam());
	}
	
	function getSubAlbums(){
		$albums=array();
		$handle=opendir(PICS_PATH.'/fotoalbum/'.$this->pad);
		while(false!==($file=readdir($handle))){
			if(is_dir(PICS_PATH.'/fotoalbum/'.$this->pad.$file)&&!preg_match('/^(\.|\_)(.*)$/',$file)){
				$albums[]=new Fotoalbum($this->getPad().$file.'/',$file);
			}
		}
		if(count($albums)>0){
			return $albums;
		}else{
			return false;
		}
	}
	
	function getFotos(){
		$fotos=array();
		$handle=opendir(PICS_PATH.'/fotoalbum/'.$this->pad);
		while(false!==($file=readdir($handle))){
			if(preg_match('/^.*\.(jpg|jpeg)$/i',$file)){
				$foto=new Foto($this->pad,$file);
				if($foto->isCompleet()){
					$fotos[]=$foto;
				}
			}
		}
		if(count($fotos)>0){
			return $fotos;
		}else{
			return false;
		}
	}
}

class Foto{
	
	private $map;
	private $bestandsnaam;
	
	function Foto($map,$bestandsnaam){
		$this->map=$map;
		$this->bestandsnaam=$bestandsnaam;
	}
	
	function getMap(){
		return $this->map;
	}
	
	function getBestandsnaam(){
		return $this->bestandsnaam;
	}
	
	function getThumbURL(){
		return CSR_PICS.'fotoalbum/'.$this->getMap().'_thumbs/'.$this->bestandsnaam;
	}
	
	function getResizedURL(){
		return CSR_PICS.'fotoalbum/'.$this->getMap().'_resized/'.$this->bestandsnaam;
	}
	
	function isCompleet(){
		return (file_exists(PICS_PATH.'/fotoalbum/'.$this->getMap().'_thumbs/'.$this->bestandsnaam) &&
				file_exists(PICS_PATH.'/fotoalbum/'.$this->getMap().'_resized/'.$this->bestandsnaam));
	}
}