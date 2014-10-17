<?php
/**
* class to "rainbow" text
*
* @package eamFunParser
*/
class rainbowMaker{
    var $colors;
    var $standard_colors;
    function rainbowMaker(){
        $this->colors = $this->standard_colors = array(
            array(255,   0,   0),
            array(255, 153,   0),
            array(255, 255,  51),  // regenboogje
            array( 51, 204,  51),
            array( 51,  51, 204),
            array(204,  51, 204)
            );  
    }
    function setColors($array){
        $this->colors = $array;
    }
    function resetColors(){
        $this->colors = $this->standard_colors;
    }
    

    function rainbow($string, $array_colors = array()){
        if(count($array_colors) <= 1){
            $array_colors = $this->colors;
        }
        $aantal_kleuren = count($array_colors);
        $aantal_overgangen = $aantal_kleuren - 1;
        $substr_length = round(strlen($string) / $aantal_overgangen);
    
        $newstring = "";
            if(strlen($string) <= $aantal_kleuren){
            for($i = 0; $i < strlen($string); $i++){
                $newstring .= '<font color="'. $this->hexColor($array_colors[i]) . '">'. substr($string, $i, 1) . '</font>';
            }
        } else {  
            
            for($i = 0; $i < $aantal_overgangen - 1 ; $i++){ // -1, want de laatste zelf uitrekenen
                $newstring .= $this->stringPart(substr($string, $i * $substr_length, $substr_length), $array_colors[$i], $array_colors[$i + 1]);
            }
            
            $newstring .= $this->stringPart(substr($string, $i * $substr_length), $array_colors[$i], $array_colors[$i + 1]);
        }     
        return $newstring;
            
            
    }       
            
    function hexColor($color)
    {       
        $r = dechex($color[0]);
        if(strlen($r) < 2)
            $r = "0".$r;
            
        $g = dechex($color[1]);
        if(strlen($g) < 2)
                $g = "0".$g;
        $b = dechex($color[2]);
        if(strlen($b) < 2)
                $b = "0".$b; 
            
        return $r.$g.$b;  
    }       
    function stringPart($string, $color1, $color2){
        $r_diff = $color2[0] - $color1[0]; 
        $g_diff = $color2[1] - $color1[1];    // The differences
        $b_diff = $color2[2] - $color1[2];
            
        $newstring = "";
            
        for($i = 0; $i < strlen($string); $i++){
            $r =  (int)$color1[0] + $i * ($r_diff / strlen($string));
            $g =  (int)$color1[1] + $i * ($g_diff / strlen($string));
            $b =  (int)$color1[2] + $i * ($b_diff / strlen($string));
            
            $hex = $this->hexColor(array($r, $g, $b));
            
            $newstring .= '<font color="#'.$hex.'">'.substr($string, $i, 1).'</font>';
        }   
        return $newstring;
    } 
}      
?>