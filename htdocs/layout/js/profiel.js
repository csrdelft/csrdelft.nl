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



jQuery(document).ready(function($) {

	jQuery("#statusForm #field_status").click( function() {
		//standaard alle velden verbergen
		$('#lidafdatum, #kring, #postfix, #ontvangtcontactueel, #echtgenoot, #adresseringechtpaar, #sterfdatum').hide();
		$(".novieten, .leden").hide();
		var status = $(this).val();
		switch(status){
			case "S_OUDLID":
			case "S_ERELID":
				$('#kring, #ontvangtcontactueel, #echtgenoot, #adresseringechtpaar').show();
			case "S_NOBODY":
			case "S_EXLID":
				$('#lidafdatum').show();

				//waardes voorinvullen
				$("#field_kring").val(status=='S_NOBODY' || status=='S_EXLID' ? 0 : original['kring']);
				
				if(original['lidafdatum_jaar']=='0000'){
					var now = new Date();
					setLidaf(now.getFullYear(), now.getMonth(), now.getDate());
				}
				$("#field_permissies").val(status=="S_NOBODY" || status=='S_EXLID' ? 'R_NOBODY' : 'R_OUDLID');
			break;
			case "S_LID":
			case "S_GASTLID":
			case "S_NOVIET":
				$('#postfix').show();
				//postfix hints weergeven
				if(status=="S_NOVIET"){
					$(".novieten").show();
				}else{
					$(".leden").show();
				}
				//waardes voorinvullen
				$("#field_permissies").val(original['permissies']=='R_OUDLID' || original['permissies']=='R_NOBODY' ? 'R_LID' : original['permissies']);
			break;
			case "S_OVERLEDEN":
				$('#lidafdatum, #sterfdatum').show();
				setLidaf(original['lidafdatum_jaar'],original['lidafdatum_maand'],original['lidafdatum_dag']);
			case "S_CIE":
			case "S_KRINGEL":
				//waardes voorinvullen
				$("#field_permissies").val(status=="S_KRINGEL" ? 'R_LID' : 'R_NOBODY');
			break;
		} //end switch(status)
		
		function setLidaf(year,month,day){
			$('#field_lidafdatum_jaar').val(year);
			$('#field_lidafdatum_maand').val(month);
			$('#field_lidafdatum_dag').val(day);
		}
	});

	//Originele waarden van een aantal velden opslaan in een array, zodat
	//we ze later nog kunnen raadplegen.
	var original=[];
	$('#field_permissies, #field_kring, #field_lidafdatum_jaar, #field_lidafdatum_maand, #field_lidafdatum_dag, #field_status')
		.each(function(){
			original[$(this).attr('id').substring(6)]=$(this).val();
		});


	//statuswijzigform: velden aanpassen aan huidige status, ook bij reset
	$("#field_status").trigger('click');
	$('#statusForm').bind("reset", function() {
		setTimeout("$('#field_status').trigger('click') ", 100);
	});
	
	//Novcie opmerkingen voor de inschrijvers
	$('#novietSoort').hide();
	  $('#matrixPlek').hide();
	  $('#startkamp').hide();
	  $('#medisch').hide();
	  $('#novitiaatBijz').hide();
	  $('#kgb').hide();
	$('#novcieKnopFormulier').click(function() {
	  $('#novietSoort').toggle('fast');
	  $('#matrixPlek').toggle('fast');
	  $('#startkamp').toggle('fast');
	  $('#medisch').toggle('fast');
	  $('#novitiaatBijz').toggle('fast');
	  $('#kgb').toggle('fast');
	});

});
