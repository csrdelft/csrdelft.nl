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

		var status = $(this).val();
		switch(status){
			case "S_OUDLID":
			case "S_ERELID":
			case "S_NOBODY":
				$('#sterfdatum, #postfix').hide();
				$(".novieten, .leden").hide();

				$('#lidafdatum, #kring, #ontvangtcontactueel, #echtgenoot, #adresseringechtpaar').show();
				
				if(status=="S_NOBODY"){
					$('#ontvangtcontactueel, #echtgenoot, #adresseringechtpaar, #kring').hide();
				}
				
				//waardes voorinvullen
				$("#field_kring").val(status=='S_NOBODY' ? 0 : original['kring']);
				
				if(original['lidafdatum_jaar']=='0000'){
					var now = new Date();
					setLidaf(now.getFullYear(), now.getMonth(), now.getDate());
				}
				if(status=="S_NOBODY"){
					$("#field_permissies").val('P_NOBODY');
				}else{
					$("#field_permissies").val('P_OUDLID');
				}
			break;
			case "S_LID":
			case "S_GASTLID":
			case "S_NOVIET":
				$('#lidafdatum, #kring, #ontvangtcontactueel, #echtgenoot, #adresseringechtpaar, #sterfdatum').hide();
				
				$('#postfix').show();
				
				if(status=="S_NOVIET"){
					$(".leden").hide();
					$(".novieten").show();
				}else if(status=="S_LID" || status=="S_GASTLID"){
					$(".novieten").hide();
					$(".leden").show();
				}
				//waardes voorinvullen
				if(original['permissies']=='P_OUDLID' || original['permissies']=='P_NOBODY'){
					$("#field_permissies").val('P_LID');
				}else{
					$("#field_permissies").val(original['permissies']);
				}
			break;
			case "S_OVERLEDEN":
			case "S_CIE":
			case "S_KRINGEL":
				$('#kring, #ontvangtcontactueel, #echtgenoot, #adresseringechtpaar, #postfix').hide();
				
				$(".novieten, .leden").hide();
				
				if(status=="S_OVERLEDEN"){
					$('#lidafdatum, #sterfdatum').show();
				}else{
					$('#lidafdatum, #sterfdatum').hide;
				}
				
				//waardes voorinvullen
				if(status=="S_KRINGEL"){
					$("#field_permissies").val('P_LID');
				}else{
					$("#field_permissies").val('P_NOBODY');
				}
				if(status=="S_OVERLEDEN"){
					setLidaf(lidaf_jaar,lidaf_maand,lidaf_dag);
				}
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

});
