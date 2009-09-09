/*
 * Spul voor csrdelft.nl-profiel;
 */

function verbreedSaldografiek(cie){
	grafiek=document.getElementById(cie+'grafiek');
	url=grafiek.src.split('?');
	querystring=url[1].split('&');

	timespan=40;
	uid='';
	for(var i=0;i<querystring.length;i++){
		keyvalue=querystring[i].split('=');
		if(keyvalue[0]=='timespan'){
			timespan=keyvalue[1];
		}else if(keyvalue[0]=='uid'){
			uid=keyvalue[1]
		}
	}
	timespan=Math.ceil(timespan*1.4);
	src='http://csrdelft.nl/tools/saldografiek.php?uid='+uid+'&timespan='+timespan;
	if(cie=='maalcie'){
		src+='&maalcie';
	}
	if(timespan<1000){
		grafiek.src=src;
	}
}

function uidPreview(fieldname){
	field=document.getElementById('field_'+fieldname);
	if(field.value.length==4){
		http.abort();
		http.open("GET", "/tools/naamlink.php?uid="+field.value, true);
		http.onreadystatechange=function(){
			if(http.readyState == 4){
				document.getElementById('preview_'+fieldname).innerHTML=http.responseText;	
			}
		}
		http.send(null);
	}
	return null;
}
