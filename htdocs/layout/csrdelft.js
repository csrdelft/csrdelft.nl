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
	scripttag.src = '/forum/bewerken/formulier/'+post;
	document.body.appendChild(scripttag);
}

function youtubeDisplay(ytID){
	var html='<object width="425" height="350">' + 
		'<param name="movie" value="http://www.youtube.com/v/' + ytID + '&autoplay=1"></param>' + 
		'<embed src="http://www.youtube.com/v/' + ytID + '&autoplay=1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
	
	if(document.all){
		//hier moet een <br /> ofzo voor de <object>-tag, want anders maakt IE de div leeg ipv er iets in te zetten.
		document.all['youtube'+ytID].innerHTML ='<br />'+ html;
	}else{
		document.getElementById('youtube'+ytID).innerHTML = html;
	}
}
function updateGroepform(){
	var status=document.getElementById('groepStatus');
	var gAanmeldDiv=document.getElementById('groepAanmeldbaarContainer')
	if(status.selectedIndex==0){
		gAanmeldDiv.style.display="block";
		
		var aanmeldbaar=document.getElementById('groepAanmeldbaar');
		var gLimietDiv=document.getElementById('groepLimietContainer');
		if(aanmeldbaar.checked){
			gLimietDiv.style.display="block";
		}else{
			gLimietDiv.style.display="none";
		}
	}else{
		gAanmeldDiv.style.display="none";
	}
}
function toggleDiv(id){
	var div=document.getElementById(id);
	if(div.style.display=="none"){
		div.style.display="block";
	}else{
		div.style.display="none";
	}
}
