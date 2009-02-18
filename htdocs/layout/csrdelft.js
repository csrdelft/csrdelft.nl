/*
* csrdelft.nl javascript libje...
*/

//we maken een standaar AJAX-ding aan.
var http = false;
if(navigator.appName == "Microsoft Internet Explorer") {
  http = new ActiveXObject("Microsoft.XMLHTTP");
} else {
  http = new XMLHttpRequest();
}

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
function previewPost(source, dest){
	var post=document.getElementById(source).value;
	if(post.length!=''){
		var previewDiv=document.getElementById(dest);
		applyUBB(post, previewDiv);
		displayDiv(document.getElementById(dest+"Container"));
	}
}
/*
 * Apply UBB to a string, and put it in innerHTML of given div.
 * 
 * Example:
 * applyUBB('[url=http://csrdelft.nl]csrdelft.nl[/url]', document.getElementById('berichtPreview'));	
 */
function applyUBB(string, div){
	http.abort();
	http.open("GET", "/tools/ubb.php?string="+encodeURIComponent(string), true);
	http.onreadystatechange=function(){
		if(http.readyState == 4){
			div.innerHTML=http.responseText;
		}
	}
	http.send(null);
}

/*
 * Een post bewerken in het forum.
 * Haal een post op, bouw een formuliertje met javascript.
 */
var bewerkDiv=null;
var bewerkDivInnerHTML=null;
function forumBewerken(post){
	http.abort();
	http.open("GET", "/communicatie/forum/getPost.php?post="+post, true);
	http.onreadystatechange=function(){
		if(http.readyState == 4){
			if(document.getElementById('forumEditForm')){ restorePost(); }
			
			bewerkDiv=document.getElementById('post'+post);
			bewerkDivInnerHTML=bewerkDiv.innerHTML;
			
			bewerkForm ='<form action="/communicatie/forum/bewerken/'+post+'" method="post" id="forumEditForm">';
			bewerkForm+='<h3>Bericht bewerken</h3>Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]<br />';
			bewerkForm+='<div id="bewerkPreviewContainer" class="previewContainer"><h3>Voorbeeld van uw bericht:</h3><div id="bewerkPreview" class="preview"></div></div>';
			bewerkForm+='<textarea name="bericht" id="forumBewerkBericht" class="tekst" rows="8" style="width: 100%;"></textarea>';
			bewerkForm+='Reden van bewerking: <input type="text" name="reden" style="width: 250px;"/><br /><br />';
			bewerkForm+='<a style="float: right;" class="handje knop" onclick="toggleDiv(\'ubbhulpverhaal\')" title="Opmaakhulp weergeven">UBB</a>';
			bewerkForm+='<a style="float: right;" class="handje knop" onclick="vergrootTextarea(\'forumBewerkBericht\', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>';
			bewerkForm+='<input type="submit" value="opslaan" /> <input type="button" value="voorbeeld" onclick="previewPost(\'forumBewerkBericht\', \'bewerkPreview\')" /> <input type="button" value="terug" onclick="restorePost()" />';
			bewerkForm+='</form>';
			
			bewerkDiv.innerHTML=bewerkForm;
			document.getElementById('forumBewerkBericht').value=http.responseText;	
			
			//invoerveldjes van het normale toevoegformulier even uitzetten.
			document.getElementById('forumBericht').disabled=true;
			document.getElementById('forumOpslaan').disabled=true;
			document.getElementById('forumVoorbeeld').disabled=true;
		}
	}
	http.send(null);
	return false;	
}
function restorePost(){
	bewerkDiv.innerHTML=bewerkDivInnerHTML;
	document.getElementById('forumBericht').disabled=false;
	document.getElementById('forumOpslaan').disabled=false;
	document.getElementById('forumVoorbeeld').disabled=false;
}
function forumCiteren(post){
	http.abort();
	http.open("GET", "/communicatie/forum/getPost.php?citaat=true&post="+post, true);
	http.onreadystatechange=function(){
		if(http.readyState == 4){
			document.getElementById('forumBericht').value+=http.responseText;
			//helemaal naar beneden scrollen.
			window.scroll(0,document.body.clientHeight);
		}
	}
	http.send(null);
	//we returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd. 
	//Het werkt dan dus nog wel als javascript uit staat.
	return false;
}
function youtubeDisplay(ytID){
	var html='<object width="425" height="350">' +
		'<param name="movie" value="http://www.youtube.com/v/' + ytID + '&autoplay=1"></param>' +
		'<embed src="http://www.youtube.com/v/' + ytID + '&autoplay=1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
	
	if(document.all){
		//hier moet een <br /> ofzo voor de <object>-tag, want anders maakt IE de div leeg ipv er iets in te zetten. 
		//2009-02-18 Jieter; dit commentaar was ergens verloren gegaan, maar het blijft een wazige aangelegenheid.
		document.all['youtube'+ytID].innerHTML ='<br />'+ html;
	}else{
		document.getElementById('youtube'+ytID).innerHTML = html;
	}
}
function youtubeDisplay(ytID){
	document.getElementById('youtube'+ytID).innerHTML='<object width="425" height="350">' + 
		'<param name="movie" value="http://www.youtube.com/v/' + ytID + '&autoplay=1"></param>' + 
		'<embed src="http://www.youtube.com/v/' + ytID + '&autoplay=1" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
	return false;
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
		if(gEindeVeld.value=='0000-00-00'){
			var now=new Date();
			gEindeVeld.value=now.getFullYear()+'-'+LZ(now.getMonth()+1)+'-'+LZ(now.getDate());
		}
	}
}
function hideDiv(div){ div.style.display="none"; }
function displayDiv(div){ div.style.display="block"; }

function toggleDiv(id){
	var div=document.getElementById(id);
	if(div.style.display=="block" || div.style.display=="inline"){
		hideDiv(div);
	}else{
		displayDiv(div);
	}
}
var orig=null;
function togglePasfotos(uids, div){
	if(orig!=null){
		div.innerHTML=orig;
		orig=null;
	}else{
		http.abort();
		http.open("GET", "/tools/pasfotos.php?string="+escape(uids), true);
		http.onreadystatechange=function(){
			if(http.readyState == 4){
				orig=div.innerHTML;
				div.innerHTML=http.responseText;
			}
		}
		http.send(null);
	}
}
//dummy fixPNG
function fixPNG(){ 
	return false; 
}