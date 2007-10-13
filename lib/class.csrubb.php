<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrubb.php
# -------------------------------------------------------------------
#  wrapper
# -------------------------------------------------------------------

require_once('ubb/eamBBParser.class.php');

class CsrUBB extends eamBBParser{
  private $lid;
  
  function CsrUBB(){
  	$this->eamBBParser();
	$this->lid=Lid::get_lid();
	$this->paragraph_mode = false;
  }
  function ubb_citaat($arguments=array()){
  	if($this->quote_level == 0){        
    	$this->quote_level = 1;
    	$content = $this->parseArray(array('[/citaat]'), array());
    	$this->quote_level = 0;
    } else {
    	$this->quote_level++;
    	$delcontent = $this->parseArray(array('[/citaat]'), array());
    	$this->quote_level--;
    	unset($delcontent);
    	$content = '...';
    }
	
    $text='<div class="citaatContainer"><strong>Citaat';
	if(isset($arguments['citaat']) AND $this->lid->isValidUid($arguments['citaat'])){
		$text.=' van '.$this->lid->getNaamLink($arguments['citaat'], 'user', true);
	}elseif(trim($arguments['citaat'])!=''){
		$text.=' van '.$arguments['citaat'];
	}else{
		//geen naam ofzo...
	}
	$text.=':</strong><div class="citaat">'.trim($content).'</div></div>';
    return $text;  
  }
	function ubb_lid($parameters){
		$content = $this->parseArray(array('[br]'), array());
		array_unshift($this->parseArray, '[br]');
		if(isset($parameters['lid'])){
			$text=$this->lid->getNaamLink($parameters['lid'], 'user', true).$content;
		}else{
			$text='geen uid opgegeven';
		}
		return $text;
	}
	/* 
	 * ubb_prive().
	 * 
	 * Tekst binnen de privé-tag wordt enkel weergegeven voor leden met
	 * (standaard) P_LOGGED_IN. Een andere permissie kan worden meegegeven.
	 * 
	 * LET OP: binnen het forum is citeren mogelijk voor externen. Dan kan 
	 * de inhoud van deze tag dus bekeken worden door te citeren. Als deze 
	 * tag dus nuttig moet worden voor het forum moet bij citeren door 
	 * externen de inhoud van deze tag weggefilterd woren.
	 */
	function ubb_prive($arguments=array()){
		$lid=Lid::get_lid();
		if(isset($arguments['prive'])){
			$permissie=$arguments['prive'];
		}else{
			$permissie='P_LOGGED_IN';
		}
		//content moet altijd geparsed worden, anders blijft de inhoud van de
		//tag gewoon staan.
		$content = $this->parseArray(array('[/prive]'), array());
		if(!$lid->hasPermission($permissie)){
			$content='';
		}
		return $content;
	}
	 
	 
	/*
	 * Deze methode kan resultaten van query's die in de database staan printen in een 
	 * tabelletje.
	 */
	function ubb_query($parameters){
		if(isset($parameters['query'])){
			require_once('class.savedquery.php');
			$query=new SavedQuery((int)$parameters['query']);
			$return=$query->getHtml();
		}else{
			$return='Geen geldige query';
		}
		return $return;
	}
	
	function ubb_youtube($parameters){
		$content = $this->parseArray(array('[/youtube]'), array());
		$html='<object width="425" height="350">' .
				'	<param name="movie" value="http://www.youtube.com/v/'.$content.'"></param>' .
				'	<param name="wmode" value="transparent"></param>' .
				'	<embed src="http://www.youtube.com/v/'.$content.'" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed>' .
				'</object>';
		return $html;
	}
	function ubb_1337(){
        $html = $this->parseArray(array('[/1337]'), array());
        
        $html = str_replace('er ', '0r ',$html);
        $html = str_replace('you', 'j00',$html);
        $html = str_replace('elite', '1337',$html);
        $html = strtr($html, "abelostABELOST", "48310574831057");       
        return $html;
    }
    function ubb_rainbow(){
        $string = $this->parseArray(array('[/rainbow]'), array());
        
        if(!@include_once("ubb/plugins/rainbow.php")){
             return '<b>Rainbow plugin could not be loaded!</b>';
        }
        
        $r = new rainbowMaker();
        
        return $r->rainBow($string);
    }
  
	static function viewUbbHelp(){
echo <<<UBBVERHAAL
<div id="ubbhulp">
	<a href="#laatste" onclick="document.getElementById('ubbhulpverhaal').style.display = 'block'">Opmaakhulp weergeven</a><br />
</div>
<div id="ubbhulpverhaal">
	<span id="ubbsluiten" onclick="document.getElementById('ubbhulpverhaal').style.display = 'none'" title="Opmaakhulp verbergen">&times;</span>
	<h2>Tekst opmaken</h2>
	U kunt uw berichten opmaken met een simpel opmaaktaaltje wat ubb genoemd wordt. Het lijkt wat op html, maar dan met vierkante haken:<br />
	<ul>
		<li>[b]...[/b] voor <strong>vette tekst</strong></li>
		<li>[i]...[/i] voor <em>cursieve tekst</em></li>
		<li>[u]...[/u] voor <span style="text-decoration: underline;">onderstreepte tekst</span></li>
		<li>[s]...[/s] voor <s>doorgestreepte tekst</s></li>
		<li>[url=http://csrdelft.nl]Webstek van C.S.R.[/url] voor een verwijzing</li>
		<li>[img]http://csrdelft.nl/plaetje.jpg[/img] voor een plaetje</li>
		<li>[citaat][/citaat] voor een citaat. [citaat=<em>lidnummer</em>][/citaat] voor een citaat van een lid.</li>
		<li>[lid=<em>lidnummer</em>] voor een link naar het profiel van een lid of oudlid</li>
		<li>[youtube]<em>youtube-id</em>[/youtube] voor een filmpje direct in je post</li>
		<li>[ubboff]...[/ubboff] voor een stukje met ubb-tags zonder dat ze ge&iuml;nterpreteerd worden</li>
	</ul>
	Gebruik deze mogelijkheden spaarzaam, ga niet ineens alles vet maken of kleurtjes geven!<br />
	<br />
	
</div>
UBBVERHAAL;
	
	}

}

?>
