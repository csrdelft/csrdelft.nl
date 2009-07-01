function roodschopper(actie){
	http.abort();

	var form=document.getElementById('roodschopper');
	var params=new Array();
	params.push('actie='+encodeURIComponent(actie));
	
	for(i=0; i<form.elements.length; i++){
		if(form.elements[i].type=='select-one' || form.elements[i].type=='text' || form.elements[i].type=='textarea'){
			params.push(form.elements[i].name+"="+encodeURIComponent(form.elements[i].value));
			form.elements[i].disabled=true;			
		}
	}
	console.log(params);

	http.open("POST", "/tools/roodschopper.php", true);
	http.setRequestHeader("Content-length", params.length);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Connection", "close");

	http.onreadystatechange=function(){
		if(http.readyState == 4){
			div=document.getElementById('messageContainer');
			div.innerHTML=http.responseText;
			displayDiv(div);
			hideDiv(document.getElementById('submitContainer'));
		}
	}
	http.send(params.join('&'));
}
function restoreRoodschopper(){
	var form=document.getElementById('roodschopper');
	for(i=0; i<form.elements.length; i++){
		if(form.elements[i].type=='select-one' || form.elements[i].type=='text' || form.elements[i].type=='textarea'){
			form.elements[i].disabled=false;			
		}
	}
	displayDiv(document.getElementById('submitContainer'));
	hideDiv(document.getElementById('messageContainer'))
}
