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
	
	//als deze regexp matched is het album alleen voor leden
	private $alleenLeden='/(novitiaat|ontvoering|feuten|slachten|zuipen)/i';

	//lazy loader-placeholders
	private $fotos=null;
	private $subalbums=null;

	function Fotoalbum($pad,$mapnaam){
		//beetje vies dit, maar er moet natuurlijk een fatsoenlijk pad uitkomen.
		if(substr($pad, 0, 1)!='/'){
			$pad='/'.$pad;
		}
		$this->pad=$pad;


		$this->mapnaam=$mapnaam;
	}

	function getPad(){
		return $this->pad;
	}

	function getFullpath(){
		return PICS_PATH.'/fotoalbum/'.$this->pad;
	}

	function getMapnaam(){
		return $this->mapnaam;
	}

	function getNaam(){
		return ucfirst($this->getMapnaam());
	}

	//bestaat er een map met de naam van het pad.
	function exists(){
		return file_exists($this->getFullpath()) && is_dir($this->getFullpath());
	}

	//file modification time van het album.
	function modified(){
		return filemtime($this->getFullpath());
	}

	function getMostrecentSubAlbum(){
		$albums=$this->getSubAlbums();

		//geen subalbums, return self
		if(!is_array($albums) || count($albums)<1){
			return $this;
		}

		$recent=$this;
		foreach($albums as $album){
			if($album->modified()>$recent->modified()){
				$recent=$album->getMostrecentSubAlbum();
			}
		}
		return $recent;
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
				if($map==''){
					continue;
				}
				$url.=urlencode($map).'/';
				$breadcrumb.=' » <a href="'.$url.'" title="'.$map.'">'.$map.'</a>';
			}
			return $breadcrumb;
		}
	}

	function getThumbURL(){

		# Foto uit album zelf
		$fotos=$this->getFotos();
		if(is_array($fotos) AND count($fotos)>0){

			//gebruik de foto eindigend op folder.jpg als die bestaat.
			foreach($fotos as $foto){
				if(substr($foto->getBestandsnaam(),-10)=='folder.jpg'){
					return $foto->getThumbURL();
				}
			}

			//anders gewoon de eerste.
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
		//lazy-loading...
		if($this->subalbums===null){
			//Mappenlijst ophalen en sorteren
			$mappen=array();

			if(is_dir($this->getFullpath())){
				$handle=opendir($this->getFullpath());
				while(false!==($file=readdir($handle))){
					if(is_dir($this->getFullpath().$file) && !preg_match('/^(\.|\_)(.*)$/',$file)){
						$mappen[]=$file;
					}
				}
				sort($mappen);
			}
			//$mappen=array_reverse($mappen);

			# Albums aanmaken en teruggeven
			$albums=array();
			foreach($mappen as $map){
				$album=new Fotoalbum($this->getPad().$map.'/', $map);
				if($album->magBekijken()){
					$albums[]=$album;
				}
			}

			if(count($albums)>0){
				$this->subalbums=$albums;
			}else{
				$this->subalbums=false;
			}
		}
		return $this->subalbums;
	}

	function getFotos($compleet=true){
		//lazy-loading...
		if($this->fotos===null){
			$bestanden=array();
			if(!$this->exists()){
				$this->fotos=false;
			}
			if(is_dir(PICS_PATH.'/fotoalbum/'.$this->pad) && $handle=opendir(PICS_PATH.'/fotoalbum/'.$this->pad)){
				while(false!==($bestand=readdir($handle))){
					$bestanden[]=$bestand;
				}
			}
			
			sort($bestanden);

			$fotos=array();
			foreach($bestanden as $bestand){
				if(preg_match('/^[^_].*\.(jpg|jpeg)$/i',$bestand)){
					$foto=new Foto($this->pad, $bestand);
					if($foto->isCompleet()==$compleet){
						$fotos[]=$foto;
					}
				}
			}
			$this->fotos=$fotos;
		}
		return $this->fotos;
	}

	function magBekijken(){
		if(LoginLid::instance()->hasPermission('P_LEDEN_READ')){
			return true;
		}else{
			//Deze foto's niet voor gewoon volk
			return (!preg_match($this->alleenLeden, $this->getPad()));
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
			if(!file_exists($this->getFullpath().'/_thumbs')){
				mkdir($this->getFullpath().'/_thumbs');
				chmod($this->getFullpath().'/_thumbs', 0755);
			}
			if(!file_exists($this->getFullpath().'/_resized')){
				mkdir($this->getFullpath().'/_resized');
				chmod($this->getFullpath().'/_resized', 0755);
				chmod($this->getFullpath().'/_resized', 0755);
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
		return CSR_PICS.'fotoalbum/'.$this->urlencode($this->getMap()).'_thumbs/'.$this->urlencode($this->getBestandsnaam());
	}

	function getResizedURL(){
		return CSR_PICS.'fotoalbum/'.$this->urlencode($this->getMap()).'_resized/'.$this->urlencode($this->getBestandsnaam());
	}

	function bestaatThumb(){
		return file_exists(PICS_PATH.'/fotoalbum/'.$this->getMap().'_thumbs/'.$this->getBestandsnaam());
	}

	function bestaatResized(){
		return file_exists(PICS_PATH.'/fotoalbum/'.$this->getMap().'_resized/'.$this->getBestandsnaam());
	}

	function maakThumb(){
		set_time_limit(0);
		$command=IMAGEMAGICK_PATH.' '.escapeshellarg($this->getPad()).' -thumbnail 150x150^^ -gravity center -extent 150x150 -format jpg -quality 80 '.escapeshellarg($this->getThumbPad()).'';
		echo $command.'<br />';
		echo shell_exec($command).'<hr />';
		chmod($this->getThumbPad(), 0644);
	}

	function maakResized(){
		set_time_limit(0);
		$command=IMAGEMAGICK_PATH.' '.escapeshellarg($this->getPad()).' -resize 800x800 -format jpg -quality 70 '.escapeshellarg($this->getResizedPad()).'';
		echo $command.'<br />';
		echo shell_exec($command).'<hr />';
		chmod($this->getResizedPad(), 0644);
	}

	function isCompleet(){
		return ($this->bestaatThumb() && $this->bestaatResized());
	}

	function urlencode($url){
		//urlencode() maar dan de slashes niet
		return str_replace('%2F', '/', rawurlencode($url));
	}
}
