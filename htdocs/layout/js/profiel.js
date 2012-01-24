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
	//statuswijzigform: velden update na aanpassen status
	jQuery("#field_status").click( function() {
		var status = $(this).val();
		switch(status){
			case "S_OUDLID":
			case "S_ERELID":
			case "S_NOBODY":
				verberg('field_sterfdatum');
				verberg('field_postfix');
				$(".novieten").hide();
				$(".leden").hide();

				zichtbaar('field_lidafdatum');
				zichtbaar('field_kring');
				zichtbaar('field_ontvangtcontactueel');
				zichtbaar('field_echtgenoot');
				zichtbaar('field_adresseringechtpaar');
				if(status=="S_NOBODY"){
					verberg('field_ontvangtcontactueel');
					verberg('field_echtgenoot');
					verberg('field_adresseringechtpaar');
					verberg('field_kring');
				}
				//waardes voorinvullen
				$("#field_kring").val(status=='S_NOBODY'?0:kring);
				if(lidaf_jaar==0000){
					var now = new Date();
					setLidaf(now.getFullYear(),now.getMonth(),now.getDate());
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
				verberg('field_lidafdatum');
				verberg('field_kring');
				verberg('field_ontvangtcontactueel');
				verberg('field_echtgenoot');
				verberg('field_adresseringechtpaar');
				verberg('field_sterfdatum');
				
				zichtbaar('field_postfix');
				if(status=="S_NOVIET"){
					$(".leden").hide();
					$(".novieten").show();
				}else if(status=="S_LID" || status=="S_GASTLID"){
					$(".novieten").hide();
					$(".leden").show();
				}
				//waardes voorinvullen
				if(perm=='P_OUDLID'||perm=='P_NOBODY'){
					$("#field_permissies").val('P_LID');
				}else{
					$("#field_permissies").val(perm);
				}
				break;
			case "S_OVERLEDEN":
			case "S_CIE":
			case "S_KRINGEL":
				verberg('field_kring');
				verberg('field_ontvangtcontactueel');
				verberg('field_echtgenoot');
				verberg('field_adresseringechtpaar');
				$(".novieten").hide();
				$(".leden").hide();
				verberg('field_postfix');
				if(status=="S_OVERLEDEN"){
					zichtbaar('field_lidafdatum');
					zichtbaar('field_sterfdatum');
				}else{
					verberg('field_lidafdatum');
					verberg('field_sterfdatum');
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
		}
		function setLidaf(year,month,day){
			$("select[name='lidafdatum_jaar']").val(year);
			$('select[name="lidafdatum_maand"]').val(month);
			$('select[name="lidafdatum_dag"]').val(day);
		}
		function verberg(id){ $("#"+id).parent().hide(); }
		function zichtbaar(id){ $("#"+id).parent().show(); }
	});

	//statuswijzigform: originele waarden opslaan
	var perm = $("#field_permissies").val();
	var kring = $("#field_kring").val();
	var lidaf_jaar = $("select[name='lidafdatum_jaar']").val();
	var lidaf_maand = $('select[name="lidafdatum_maand"]').val();
	var lidaf_dag = $('select[name="lidafdatum_dag"]').val();
	var status_or = $("#field_status").val();

	//statuswijzigform: velden aanpassen aan huidige status, ook bij reset
	$("#field_status").trigger('click');
	$('#statusForm').bind("reset", function() {
		setTimeout("$('#field_status').trigger('click') ", 100);
	});

	//profielbewerkenform: suggesties bij sommige inputs
	var kerksuggesties = ['PKN','PKN Hervormd','PKN Gereformeerd','PKN Gereformeerde Bond','Hersteld Hervormd',
			'Evangelisch','Volle Evangelie Gemeente','Gereformeerd Vrijgemaakt','Nederlands Gereformeerd',
			'Christelijk Gereformeerd','Gereformeerde Gemeenten','Pinkstergemeente','Katholiek Apostolisch',
			'Vergadering van gelovigen','Rooms-Katholiek','Baptist'];
	$("#field_kerk").autocomplete(kerksuggesties, { clickFire: true, max: 20, matchContains: true });
	var landsuggesties = ['Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten'];
	$("#field_land").autocomplete(landsuggesties, { clickFire: true, max: 20, matchContains: true });
	$("#field_o_land").autocomplete(landsuggesties, { clickFire: true, max: 20, matchContains: true });
	var studiesuggesties = [ 'TU Delft - BK', 'TU Delft - CT', 'TU Delft - ET', 'TU Delft - IO', 'TU Delft - LST',
			'TU Delft - LR', 'TU Delft - MT', 'TU Delft - MST', 'TU Delft - TA', 'TU Delft - TB', 'TU Delft - TI', 'TU Delft - TN',
			'TU Delft - TW', 'TU Delft - WB', 'INHolland', 'Haagse Hogeschool', 'EURotterdam', 'ULeiden'];
	$("#field_studie").autocomplete(studiesuggesties, { clickFire: true, max: 20, matchContains: true });

});
