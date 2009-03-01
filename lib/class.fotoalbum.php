<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.fotoalbum.php
# -------------------------------------------------------------------
# Dataklassen voor het fotoalbum
# -------------------------------------------------------------------


class Fotoalbum{

	private $_lid;

	private $pad;
	private $mapnaam;

	function Fotoalbum($pad,$mapnaam){
		$this->_lid=Lid::instance();

		$this->pad=$pad;
		$this->mapnaam=$mapnaam;
	}

	function getPad(){
		return $this->pad;
	}

	function getMapnaam(){
		return $this->mapnaam;
	}

	function getNaam(){
		return ucfirst($this->getMapnaam());
	}

	function getBreadcrumb(){
		if($this->getPad()==''){
			return '';
		} else {
			$breadcrumb='<a href="/actueel/fotoalbum/">Fotoalbum</a>';
			$url='/actueel/fotoalbum/';
			$mappen=explode('/',$this->getPad());
			array_pop($mappen);
			array_pop($mappen);
			foreach($mappen as $map){
				$url.=urlencode($map).'/';
				$breadcrumb.=' » <a href="'.$url.'" title="'.$map.'">'.$map.'</a>';
			}
			return $breadcrumb;
		}
	}

	function getThumbURL(){
		# Foto uit album zelf
		$fotos=$this->getFotos();
		if($fotos!==false){
			$foto=$fotos[0];
			return $foto->getThumbURL();
		}

		# Foto uit subalbum
		$albums=$this->getSubAlbums();
		if($albums!==false){
			foreach($albums as $album){
				return $album->getThumbURL();
			}
		}

		return CSR_PICS.'fotoalbum/_geen_thumb.jpg';
	}

	function getSubAlbums(){
		# Mappenlijst ophalen en sorteren
		$mappen=array();
		$handle=opendir(PICS_PATH.'/fotoalbum/'.$this->pad);
		while(false!==($file=readdir($handle))){
			if(is_dir(PICS_PATH.'/fotoalbum/'.$this->pad.$file)&&!preg_match('/^(\.|\_)(.*)$/',$file)){
				$mappen[]=$file;
			}
		}
		sort($mappen);
		//$mappen=array_reverse($mappen);

		# Albums aanmaken en teruggeven
		$albums=array();
		foreach($mappen as $map){
			$album=new Fotoalbum($this->getPad().$map.'/',$map);
			if($album->magBekijken()){
				$albums[]=$album;
			}

		}
		if(count($albums)>0){
			return $albums;
		}else{
			return false;
		}
	}

	function getFotos($compleet=true){
		$fotos=array();
		$handle=opendir(PICS_PATH.'/fotoalbum/'.$this->pad);
		while(false!==($file=readdir($handle))){
			if(preg_match('/^[^_].*\.(jpg|jpeg)$/i',$file)){
				$foto=new Foto($this->pad,$file);
				if($foto->isCompleet()==$compleet){
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

	function magBekijken(){
		if($this->_lid->hasPermission('P_LEDEN_READ')){
			return true;
		}else{
			return(!preg_match('/novitiaat/i', $this->getPad()));
		}

	}

	function verwerkFotos(){
		# Subalbums
		$albums=$this->getSubAlbums();
		if($albums!==false){
			foreach($albums as $album){
				$album->verwerkFotos();
			}
		}

		# Foto's
		$fotos=$this->getFotos(false);
		if($fotos!==false){
			# Controleren of _thums en _resized bestaan, zo niet dan maken
			if(!file_exists(PICS_PATH.'/fotoalbum/'.$this->getPad().'/_thumbs')){
				mkdir(PICS_PATH.'/fotoalbum/'.$this->getPad().'/_thumbs');
				chmod(PICS_PATH.'/fotoalbum/'.$this->getPad().'/_thumbs', 0755);
			}
			if(!file_exists(PICS_PATH.'/fotoalbum/'.$this->getPad().'/_resized')){
				mkdir(PICS_PATH.'/fotoalbum/'.$this->getPad().'/_resized');
				chmod(PICS_PATH.'/fotoalbum/'.$this->getPad().'/_resized', 0755);
			}

			# Thumbnails en resizeds maken
			foreach($fotos as $foto){
				if(!$foto->bestaatThumb()){
					$foto->maakThumb();
				}
				if(!$foto->bestaatResized()){
					$foto->maakResized();
				}
			}
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

	function getPad(){
		return PICS_PATH.'/fotoalbum/'.$this->getMap().$this->getBestandsnaam();
	}

	function getThumbPad(){
		return PICS_PATH.'/fotoalbum/'.$this->getMap().'_thumbs/'.$this->getBestandsnaam();
	}

	function getResizedPad(){
		return PICS_PATH.'/fotoalbum/'.$this->getMap().'_resized/'.$this->getBestandsnaam();
	}

	function getThumbURL(){
		return CSR_PICS.'fotoalbum/'.$this->getMap().'_thumbs/'.$this->getBestandsnaam();
	}

	function getResizedURL(){
		return CSR_PICS.'fotoalbum/'.$this->getMap().'_resized/'.$this->getBestandsnaam();
	}

	function bestaatThumb(){
		return file_exists(PICS_PATH.'/fotoalbum/'.$this->getMap().'_thumbs/'.$this->getBestandsnaam());
	}

	function bestaatResized(){
		return file_exists(PICS_PATH.'/fotoalbum/'.$this->getMap().'_resized/'.$this->getBestandsnaam());
	}

	function maakThumb(){
		set_time_limit(0);
		$command=IMAGEMAGICK_PATH.'convert '.escapeshellarg($this->getPad()).' -thumbnail 150x150^^ -gravity center -extent 150x150 -format jpg -quality 80 '.escapeshellarg($this->getThumbPad()).'';
		echo $command.'<br />';
		echo shell_exec($command).'<hr />';
		chmod($this->getThumbPad(), 0644);
	}

	function maakResized(){
		set_time_limit(0);
		$command=IMAGEMAGICK_PATH.'convert '.escapeshellarg($this->getPad()).' -resize 800x800 -format jpg -quality 70 '.escapeshellarg($this->getResizedPad()).'';
		echo $command.'<br />';
		echo shell_exec($command).'<hr />';
		chmod($this->getResizedPad(), 0644);
	}

	function isCompleet(){
		return ($this->bestaatThumb() && $this->bestaatResized());
	}
}
