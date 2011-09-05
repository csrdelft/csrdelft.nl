/*
 * Spul voor csrdelft.nl-groepenketzer.
 */

function updateGroepform(){
	var gAanmeldDiv=document.getElementById('groepAanmeldbaarContainer');
	if(document.getElementById('groepStatus').value=='ht'){
		displayDiv(gAanmeldDiv);
		var gLimietDiv=document.getElementById('groepLimietContainer');

		if(document.getElementById('groepAanmeldbaar').value!=''){
			displayDiv(gLimietDiv);
			//eventueel een opmerking weergeven bij de gekozen optie in de select.
			switch(document.getElementById('toonFuncties').selectedIndex){
				case 1:
					displayDiv(document.getElementById('functieOpmTonenzonderinvoer'));
					hideDiv(document.getElementById('functieOpmVerbergen'));
					hideDiv(document.getElementById('functieOpmNiet'));
				break;
				case 2:
					displayDiv(document.getElementById('functieOpmVerbergen'));
					hideDiv(document.getElementById('functieOpmNiet'));
					hideDiv(document.getElementById('functieOpmTonenzonderinvoer'));
				break;
				case 3:
					displayDiv(document.getElementById('functieOpmNiet'));
					hideDiv(document.getElementById('functieOpmVerbergen'));
					hideDiv(document.getElementById('functieOpmTonenzonderinvoer'));				
				break;
				default:
					hideDiv(document.getElementById('functieOpmVerbergen'));
					hideDiv(document.getElementById('functieOpmNiet'));
					hideDiv(document.getElementById('functieOpmTonenzonderinvoer'));
			}
		}else{
			hideDiv(gLimietDiv);
			hideDiv(document.getElementById('functieOpmVerbergen'));
			hideDiv(document.getElementById('functieOpmNiet'));
			hideDiv(document.getElementById('functieOpmTonenzonderinvoer'));
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
	window.location.hash='#'+tabid;

	//request doen voor de tab-inhoud
	http.abort();
	http.open("GET", "/actueel/groepen/XHR/"+groepid+"/"+tabid, true);
	http.onreadystatechange=function(){
		if(http.readyState == 4){
			document.getElementById('ledenvangroep'+groepid).innerHTML=http.responseText;
			observeClick();
		}
	};
	http.send(null);

}

jQuery(document).ready(function(){
	observeClick();
})

function observeClick(){
	jQuery(".inline_edit").click(function(){
		//show edit field.
		jQuery(this).children('span').hide();
		jQuery(this).children('input,select').show();
	}).change(function(){
		//id = 'bewerk_<gid>|<uid>'
		var ids=jQuery(this).attr('id').substring(7).split('|');
		var gid=ids[0];
		var uid=ids[1];
		var values=[];
		jQuery(this).children('input,select').each(function(index){
			values.push(jQuery(this).val());
		});
		var data = {'functie[]': values}
		//update span
		jQuery(this).children('span').html(values.join(" - "));
		
		jQuery.ajax({
			type: "POST",
			url: '/actueel/groepen/XHR/'+gid+'/bewerkfunctieLid/'+uid,
			data: data,
			cache: false,
			success: function(response){
				jQuery(".editbox").hide();
				jQuery(".text").show();
			}
		});
	});

	// close editor if clicking outside editfield
	jQuery(document).mouseup(function(object){
		if(!jQuery(object.target).hasClass("editbox")){ //in editbox mag je klikken
			jQuery(".editbox").hide();
			jQuery(".text").show();
		}
	});

};
