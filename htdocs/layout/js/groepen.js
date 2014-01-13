/*
 * Spul voor csrdelft.nl-groepenketzer.
 */

function updateGroepform(){
	var gAanmeldDiv=document.getElementById('groepAanmeldbaarContainer');
	if(document.getElementById('groepStatus').value=='ht'){
		$(gAanmeldDiv).show();
		var gLimietDiv=document.getElementById('groepLimietContainer');

		if(document.getElementById('groepAanmeldbaar').value!=''){
			$(gLimietDiv).show();
			//eventueel een opmerking weergeven bij de gekozen optie in de select.
			switch(document.getElementById('toonFuncties').selectedIndex){
				case 1:
					$('#functieOpmTonenzonderinvoer').show();
					$('#functieOpmVerbergen').hide();
					$('#functieOpmNiet').hide();
				break;
				case 2:
					$('#functieOpmVerbergen').show();
					$('#functieOpmNiet').hide();
					$('#functieOpmTonenzonderinvoer').hide();
				break;
				case 3:
					$('#functieOpmNiet').show();
					$('#functieOpmVerbergen').hide();
					$('#functieOpmTonenzonderinvoer').hide();
				break;
				default:
					$('#functieOpmVerbergen').hide();
					$('#functieOpmNiet').hide();
					$('#functieOpmTonenzonderinvoer').hide();
			}
		}else{
			$(gLimietDiv).hide();
			$('#functieOpmVerbergen').hide();
			$('#functieOpmNiet').hide();
			$('#functieOpmTonenzonderinvoer').hide();
		}
	}else{
		$(gAanmeldDiv).hide();
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

			// naar aanleiding van red-of-slacht-kip-donacie-actie
			// http://csrdelft.nl/communicatie/forum/onderwerp/6760/1
			var $table = $(".query_table"),
				total = 0.0;
			$table.find("tr:has(th:contains(opmerking))").last().nextAll().each(function(){
				total += parseFloat($(this).find("td:first-child").html().replace(",",".")) * parseFloat($(this).find("td:last-child").html());
			});
			if (typeof total === "number" && !isNaN(total) && total > 0.01){
				var omschrijving = "opmerkingen som";
				if (groepid == "1675")
					omschrijving = "Red dat kippetje!";
				else if (groepid == "1674")
					omschrijving = "Slacht dat ondier!";
				$table.append('<tr><th colspan="2">'+omschrijving+'</th></tr><tr><td colspan="2">'+total.toFixed(2)+'</td></tr>');
			}

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
