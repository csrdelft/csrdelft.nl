<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.kaart.php
# -------------------------------------------------------------------
# Maakt een google map.
# -------------------------------------------------------------------
# Historie:
# 11-03-2006
# . gemaakt
#
class Kaart extends simpleHtml{

	var $_db;
	
	function Kaart(&$db){
		$this->_db=&$db;
	}
	function view(){
		error_reporting('E_ALL');
		require_once('google-map/GoogleMapAPI.class.php');

    $map = new GoogleMapAPI('map');
   
    // enter YOUR Google Map Key
    $map->setAPIKey('ABQIAAAATQu5ACWkfGjbh95oIqCLYxQWkgeNX1N2iu_Kgi0WXwSO_-9OqRSexWKK_-sCEBQZrXHfGPD03pDkFQ');
    
    //instellingen
    $map->setMapType('satellite');
    $map->setInfoWindowTrigger('mouseover');
    $map->disableOnLoad();
		$map->disableDirections();
		//$map->disableTypeControls();
		$map->disableSidebar();
		
    $map-> addMarkerByCoords(52.0061,4.3605, 'Confide'. '<br /><b>Socie&euml;teit Confide</b>');

    // create some map markers

   
    echo '<div id="map" style="width: 500px; height: 500px"></div>';
		//
  // $map->printMap(); 
   /* </td><td>
    <?php $map->printSidebar(); ?>
    </td></tr>
    </table>
    </body>
    </html>*/
    
    $map->printHeaderJS();
   	$map->printMapJS(); 
  }
}

?>
