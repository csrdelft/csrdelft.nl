/*
 * csrdelft.nl sneltoetsen.
 */
jQuery(document).ready(function(){
	$(document).keypress(function(event){
		
		//geen sneltoetsen met modifiers
		if(event.ctrlKey || event.altKey || event.shiftKey){
			return;
		}
		
		//Geen sneltoetsen als we in een input-element of text-area zitten.
		var element = event.target;
		if (element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') {
			return;
		}
		
		//dispatch
		switch(event.charCode){
			case 98: //b voor besturen
				location.href = "http://csrdelft.nl/actueel/groepen/Besturen/";
			break;
			case 102: //f voor forum
				location.href = "http://csrdelft.nl/forum/recent";
			break;
			case 100: //d voor documenten
				location.href = "http://csrdelft.nl/communicatie/documenten/";
			break;
			case 104: //h voor thuis
				location.href = "http://csrdelft.nl/";
			break;
			case 105: //i voor instellingen
				location.href = "http://csrdelft.nl/instellingen";
			break;
			case 109: //m voor mededelingen
				location.href = "http://csrdelft.nl/actueel/mededelingen";
			break;
			case 112: //p voor profiel
				location.href = "http://csrdelft.nl/communicatie/profiel.php";
			break;
			case 122: // z voor focus naar het ledenzoekveldje.
				jQuery('#zoekveld').focus();
			break;
		}
	});
});

