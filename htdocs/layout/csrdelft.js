/*
* csrdelft.nl javascript libje...
*/

function vergrootTextarea(id, rows) {
  var textarea = document.getElementById(id);
  //if (!textarea || (typeof(textarea.rows) == "undefined")) return;
  var currentRows=textarea.rows;
  textarea.rows = currentRows + rows;
}
function setjs() {
 if(navigator.product == 'Gecko') {
   document.loginform["interface"].value = 'mozilla';
 }else if(window.opera && document.childNodes) {
   document.loginform["interface"].value = 'opera7';
 }else if(navigator.appName == 'Microsoft Internet Explorer' &&
    navigator.userAgent.indexOf("Mac_PowerPC") > 0) {
    document.loginform["interface"].value = 'konqueror';
 }else if(navigator.appName == 'Microsoft Internet Explorer' &&
 document.getElementById && document.getElementById('ietest').innerHTML) {
   document.loginform["interface"].value = 'ie';
 }else if(navigator.appName == 'Konqueror') {
    document.loginform["interface"].value = 'konqueror';
 }else if(window.opera) {
   document.loginform["interface"].value = 'opera';
 }
}
function nickvalid() {
   var nick = document.loginform.Nickname.value;
   if(nick.match(/^[A-Za-z0-9\[\]\{\}^\\\|\_\-`]{1,32}$/))
      return true;
   alert('Kies een geldige nickname!');
   //document.loginform.Nickname.value = nick.replace(/[^A-Za-z0-9\[\]\{\}^\\\|\_\-`]/g, '');
   return false;
}
function setcharset() {
	if(document.charset && document.loginform["Character set"])
		document.loginform['Character set'].value = document.charset
}
function bevestig(tekst){
	return confirm(tekst);
}

function forumEdit(post){
	var scripttag=document.createElement('SCRIPT');
	scripttag.type = 'text/javascript';
	scripttag.src = '/communicatie/forum/bewerken/formulier/'+post;
	document.body.appendChild(scripttag);
	document.getElementById('forumBericht').disabled=true;
	document.getElementById('forumOpslaan').disabled=true;
	document.getElementById('forumVoorbeeld').disabled=true;
}

function youtubeDisplay(ytID){
	var html='<object width="425" height="350">' + 
		'<param name="movie" value="http://www.youtube.com/v/' + ytID + '&autoplay=1"></param>' + 
		'<embed src="http://www.youtube.com/v/' + ytID + '&autoplay=1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
	
	if(document.all){
		document.all['youtube'+ytID].innerHTML ='<br />'+ html;
	}else{
		document.getElementById('youtube'+ytID).innerHTML = html;
	}
}
function LZ(x) {return(x<0||x>9?"":"0")+x}

function updateGroepform(){
	var gAanmeldDiv=document.getElementById('groepAanmeldbaarContainer');
	if(document.getElementById('groepStatus').selectedIndex==0){
		displayDiv(gAanmeldDiv);
		var gLimietDiv=document.getElementById('groepLimietContainer');

		if(document.getElementById('groepAanmeldbaar').checked){
			displayDiv(gLimietDiv);
			//eventueel een opmerking weergeven bij de gekozen optie in de select.
			switch(document.getElementById('toonFuncties').selectedIndex){
				case 1:
					displayDiv(document.getElementById('functieOpmVerbergen'));
					hideDiv(document.getElementById('functieOpmNiet'));
				break;
				case 2:
					displayDiv(document.getElementById('functieOpmNiet'));
					hideDiv(document.getElementById('functieOpmVerbergen'));
				break;
				default:
					hideDiv(document.getElementById('functieOpmVerbergen'));
					hideDiv(document.getElementById('functieOpmNiet'));
			}
		}else{
			hideDiv(gLimietDiv);
			hideDiv(document.getElementById('functieOpmVerbergen'));
			hideDiv(document.getElementById('functieOpmNiet'));
		}
	}else{
		hideDiv(gAanmeldDiv);
		var gEindeVeld=document.getElementById('einde');
		if(gEindeVeld.value='0000-00-00'){
			var now=new Date();
			//getYear geeft jaren na 1900 terug.
			gEindeVeld.value=(now.getYear()+1900)+'-'+LZ(now.getMonth())+'-'+LZ(now.getDate());
		}
	}
}
function hideDiv(div){ div.style.display="none"; }
function displayDiv(div){ div.style.display="block"; }

function toggleDiv(id){
	var div=document.getElementById(id);
	if(div.style.display!="block"){
		displayDiv(div);
	}else{
		hideDiv(div);
	}
}
