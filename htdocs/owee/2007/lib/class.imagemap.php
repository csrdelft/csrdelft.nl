<?php

class Imagemap{
	#identificatie in id=" vars
	var $_sMapID;
	
	#Gegevens van het plaatje
	var $_sPathToImage;
	var $_sImageWidth;
	var $_sImageHeight;
	
	#array met de area's
	var $_aAreas=array();
	
	#gegevens over de overlay
	var $_bWide=false;
	
	var $_popupWidth=280;
	var $_popupHeight=400;
	
	var $_popupPositionX=400;
	var $_popupPositionY=80;
	
	
	var $_sError=false;
	
	function Imagemap($sMapID, $sPathToImage, $bWide=false){
		$this->_sMapID=$sMapID;
		if(file_exists($sPathToImage)){
			$this->_sPathToImage=$sPathToImage;
			//hoogte een breedte opslaen
			$aImageData=getImageSize($this->_sPathToImage);
			$this->_sImageWidth=$aImageData[0];
			$this->_sImageHeight=$aImageData[1];
		}else{
			echo 'afbeelding niet gevonden';
		}
		if($bWide===true){
			$this->_bWide=true;
			$this->_popupWidth=600;
			$this->_popupPositionX=$this->_popupPositionX-280;
		}
	}
	function addArea($sID, $sOverlayText, $sCoords, $sPopupTekst, $bVisible=false){
		$this->_aAreas[]=array(
			'id' => trim($sID),
			'overlayText' => addslashes(ucfirst(trim($sOverlayText))),
			'coords' => $sCoords, 
			'popupTekst' => trim($sPopupTekst),
			'visible' => $bVisible);
	}
	function setOverlayPosition($iX, $iY){
		#is er al een popup maat? anders standaard maken
		if($this->_popupWidth==0 AND $this->_popupHeight==0){ $this->_setPopupSize(200, 400); }
		#controleer of de overlay nogwel binnen het plaatje valt
		if(($iX<=$this->_sImageWidth AND ($iX+$this->_popupWidth)<=$this->_sImageWidth) AND
			 ($iY<=$this->_sImageHeight AND ($iY+$this->_popupHeight)<=$this->_sImageHeight) ){
			$this->_popupPositionX=abs((int)$iX);
			$this->_popupPositionY=abs((int)$iY);
			return true;
		}else{
			echo 'Overlaypunten vallen buiten gebied van het plaatje';
			return false;
		}
	}
	function setPopupSize($iWidth, $iHeight){
		
	}
	
 	function getImagemap($sKoptekst){
 		$sMap=$this->getHiddenDivs();
 		$sMap.='<div id="imagemapContainer">'.$sKoptekst;
 		$sMap.='<img id="'.$this->_sMapID.'_img" src="'.$this->_sPathToImage.'"  usemap="#'.$this->_sMapID.'_map" alt="'.$this->_sMapID.'" />';
 		$sMap.='<map name="'.$this->_sMapID.'_map" id="'.$this->_sMapID.'_map">';
 		foreach($this->_aAreas as $aArea){
			$sMap.='<area shape="poly" href="#" title="" alt="" coords="'.$aArea['coords'].'"
			onclick="latenZien(\'map_'.$aArea['id'].'\');"  
			onmouseover="return overlib(\''.$aArea['overlayText'].'\', CSSW3C);" 
			onmouseout="return nd();"
			/>'."\r\n";
		}
		$sMap.='</map>';
		
		$sMap.='</div>'; // einde imagemapContainer
		return $sMap;
 	}
	function getHiddenDivs(){
 		$sMap='<div id="positionContainer" ';
 		if($this->_bWide===true){ $sMap.='style="left: 25%;" ';}
 		$sMap.='>';
 		foreach($this->_aAreas as $aArea){ 
			$sMap.='
<div id="map_'.$aArea['id'].'"  >
	<span class="afsluiten" onclick="sluiten(\'map_'.$aArea['id'].'\');" title="afsluiten">&times;</span>
		'.$aArea['popupTekst'].'
</div>'."\r\n";
		}
		$sMap.="</div>\r\n";
		return $sMap;
 	}
	
	function getCss(){
		$css='';
		foreach($this->_aAreas as $aArea){
			$css.="\r\n".
			'#map_'.$aArea['id'].'{
				display: ';	if($aArea['visible']){ $css.='block'; }else{ $css.='none'; } $css.=';
				position: absolute;
				width: '.$this->_popupWidth.'px; height: '.$this->_popupHeight.'px;
				margin: 0px; padding: 10px;
				background-color: white;
				color: black; opacity:.90; filter: alpha(opacity=90);
				overflow: auto;
				text-align: justify;
			}';
		}
		reset($this->_aAreas);
		return $css;
	}
	function getJavascript(){
		$sJavascript=<<<EOJ
		<!--
function latenZien(id) {
	if(document.getElementById(id).style.display=="block"){
		document.getElementById(id).style.display = "none";
	}else{
		var divs = document.getElementsByTagName('div');
		var leeg = true;
		for(i=0;i<divs.length;i++){
			if(divs[i].id.match(id)){
				divs[i].style.display="block";
				leeg=false;
			} else {
				if(divs[i].id.substring(0, 4)=="map_"){
					divs[i].style.display="none";
				}
			}
		}
		if(leeg){
			document.getElementById('positionContainer').style.display = "none";
		}else{
			document.getElementById('positionContainer').style.display = "block";
		}
	}
}
function sluiten(id){
	document.getElementById(id).style.display = "none";
	document.getElementById('positionContainer').style.display = "none";

}

//-->
EOJ;
		return $sJavascript;
	}
	
	
	function getError(){
		return $_sError;
	}
}
?>
