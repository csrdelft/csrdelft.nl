var orig;

function toggleEmails(vertkring){

	//request doen voor de tab-inhoud
	http.abort();
	http.open("GET", "/communicatie/verticalen.php?email="+vertkring, true);
	http.onreadystatechange=function(){
		if(http.readyState == 4){
			document.getElementById('leden'+vertkring).innerHTML=http.responseText;
		}
	};
	http.send(null);
}
