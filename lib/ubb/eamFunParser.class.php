<?php
/**
* Extension of the eamBBParser with some funny tags
*
* @package eamFunParser
*/

class eamFunParser extends eamBBParser
{
    var $plugindir;
    
    function eamFunParser($plugindir = 'plugins/')
    {
        $this->eamBBParser();
        
        $this->plugindir = $plugindir;
    }
    
    function bb_1337()
    {
        $html = $this->parseArray(array('[/1337]'), array());
        

        $html = str_replace('er ', '0r ',$html);
        $html = str_replace('you', 'j00',$html);
        $html = str_replace('elite', '1337',$html);
        $html = strtr($html, "abelostABELOST", "48310574831057");       


        return $html;
    }

    function bb_bork()
    {
        $html = $this->parseArray(array('[/bork]'), array());
        
        if(!@include_once($this->plugindir . "encheferizer.php")){
            return '<b>Encheferizer plugin could not be loaded!</b>';
        } else {
            $e = new encheferizer();
            return $e->encheferize($html);
        }        
    }
    
    function bb_colorize()
    {
        $string = $this->parseArray(array('[/colorize]'), array());
        $returnstring = "";
        for($i = 0; $i < strlen($string); $i++){
            $r = rand(0,255);
            $g = rand(0,255);
            $b = rand(0,255);
            
            $r = dechex($r);
                if(strlen($r) < 2)
                    $r = "0".$r;
            
            $g = dechex($g);
                if(strlen($g) < 2)
                    $g = "0".$g;
            $b = dechex($b);
                if(strlen($b) < 2)
                    $b = "0".$b;
            
            $returnstring.= '<font color="#'.$r.$g.$b.'">'.substr($string, $i, 1).'</font>';
        }   
    
    return $returnstring;
    
    }
    function bb_rainbow()
    {
        $string = $this->parseArray(array('[/rainbow]'), array());
        
        
        if(!@include_once($this->plugindir. "rainbow.php")){
             return '<b>Rainbow plugin could not be loaded!</b>';
        }
        
        $r = new rainbowMaker();
        
        return $r->rainBow($string);
    }
    
    
}
?>