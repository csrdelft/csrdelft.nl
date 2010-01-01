/*
 * Spul voor csrdelft.nl-groepenketzer.
 */

function updateGroepform(){
	var gAanmeldDiv=document.getElementById('groepAanmeldbaarContainer');
	if(document.getElementById('groepStatus').selectedIndex==0){
		displayDiv(gAanmeldDiv);
		var gLimietDiv=document.getElementById('groepLimietContainer');

		if(document.getElementById('groepAanmeldbaar').value!=''){
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
/*
 * showTab()
 * tabid is meteen de actie die aangeroepen wordt, een tabje erbij is
 * dus een kwestie van een nieuwe function action_<naam>(){} maken in de
 * controller
 */
function showTab(groepid, tabid){
	//alle tabjes inactief maken
	var tabs=document.getElementById('tabs').childNodes;
	for(var tabI in tabs){
		if(tabs[tabI].tagName=='LI'){
			tabs[tabI].className='';
		}
	}
	//huidige actief maken.
	document.getElementById(tabid).className='active';

	//request doen voor de tab-inhoud
	http.abort();
	http.open("GET", "/actueel/groepen/XHR/"+groepid+"/"+tabid, true);
	http.onreadystatechange=function(){
		if(http.readyState == 4){
			document.getElementById('ledenvangroep'+groepid).innerHTML=http.responseText;
		}
	};
	http.send(null);
}
