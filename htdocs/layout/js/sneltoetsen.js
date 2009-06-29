
var isMod=false;

$(document).observe("keyup", function(event){
	if(event.keyCode==16 || event.keyCode==17 || event.keyCode==18){
		isMod=false;
	}
});
$(document).observe("keydown", function(event){
	//alert(event.keyCode);
	if(event.keyCode==16 || event.keyCode==17 || event.keyCode==18){
		isMod=true;
		return;
	}
	//als er shift, control of alt ingedrukt is doen we niets.
	if(isMod){
		return;
	}
	
	//Geen sneltoetsen als we in een input-element of text-area zitten.
	var element = event.element();
    if (element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') {
		return;
	}
	
	if(event.keyCode==66){ //66 = b voor besturen.
		location.href = "http://csrdelft.nl/actueel/groepen/Besturen/";
		event.stop();
	}
	if(event.keyCode==68){ //68 = d voor documenten
		location.href = "http://csrdelft.nl/communicatie/documenten/";
		event.stop();
	}
	if(event.keyCode==70){ //70 = f voor forum
		location.href = "http://csrdelft.nl/communicatie/forum/categorie/laatste";
		event.stop();
	}
	if(event.keyCode==72){ //72 = h voor thuis
		location.href = "http://csrdelft.nl/";
		event.stop();
	}
	if(event.keyCode==73){ //73 = i voor instellingen
		location.href = "http://csrdelft.nl/instellingen";
		event.stop();
	}
	if(event.keyCode==77){ //77 = m voor mededelingen
		location.href = "http://csrdelft.nl/actueel/mededelingen";
		event.stop();
	}
	if(event.keyCode==80){ //80 = p voor profiel
		location.href = "http://csrdelft.nl/communicatie/profiel.php";
		event.stop();
	}
	if(event.keyCode==191){ //191 = / voor het ledenzoekvakje
		document.getElementById('zoekveld').focus();
		event.stop();
	}
	
	
});

