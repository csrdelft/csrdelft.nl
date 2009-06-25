$(document).observe("keydown", function(event){
	//alert(event.keyCode);
	if(event.keyCode==66){ //66 = b voor besturen.
		location.href = "http://csrdelft.nl/actueel/groepen/Besturen/";
		event.stop();
	}
	if(event.keyCode==68){ //68 = d voor documenten
		location.href = "http://csrdelft.nl/actueel/documenten/";
		event.stop();
	}
	if(event.keyCode==70){ //70 = f voor forum
		location.href = "http://csrdelft.nl/communicatie/forum/categorie/laatste";
		event.stop();
	}
	if(event.keyCode==73){ //73 = i voor instellingen
		location.href = "http://csrdelft.nl/instellingen";
		event.stop();
	}
	if(event.keyCode==77){ //77 = m voor forum
		location.href = "http://csrdelft.nl/actueel/mededelingen";
		event.stop();
	}
	if(event.keyCode==80){ //80 = p voor instellingen
		location.href = "http://csrdelft.nl/communicatie/profiel.php";
		event.stop();
	}
	
	
});

