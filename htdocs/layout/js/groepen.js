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
	
	console.log($(".edit_td"));
	
	jQuery(".edit_td").click(function(){
		var ID=jQuery(this).attr('id');
		jQuery("#functie_"+ID).hide();
		jQuery("#functie_input_"+ID).show();
	}).change(function(){
		var ID=jQuery(this).attr('id');
		var gid=jQuery("#gid_"+ID).val();
		var uid=jQuery("#uid_"+ID).val();
		var functie=jQuery("#functie_input_"+ID).val();
		var dataString = 'functie='+ functie + '&uid=' + uid + '&gid='+gid;
		$("#functie_"+ID).html('Laad...'); // Loading

		jQuery.ajax({
			type: "POST",
			url: '/actueel/groepen/XHR/' + gid + '/bewerkfunctieLid/' + uid,
			data: dataString,
			cache: false,
			success: function(result){
				jQuery("#functie_"+ID).html(result);
			}
		});
	});

	// Outside click action
	jQuery(document).mouseup(function(object){
		if(!$(object.target).hasClass("editbox")) //clicken in editbox is toegestaan
		{
			jQuery(".editbox").hide();
			jQuery(".text").show();
		}
	});

};
