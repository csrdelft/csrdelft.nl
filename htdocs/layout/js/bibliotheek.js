/*
 *	Bibliotheekjavascriptcode.
 */
jQuery(document).ready(function($) {
	//catalogus: tabellen naar zebra converteren.
	jQuery("#boeken tr:odd").addClass('odd');


	//catalogus: hippe sorteerbare tabel fixen.
	var oTableCatalogus = jQuery("#boekencatalogus").dataTable({
		"oLanguage": {
			"sZeroRecords": "Geen boeken gevonden",
			"sInfoEmtpy": "Geen boeken gevonden",
			"sSearch": "Zoeken:",
			oPaginate:{
				"sFirst": "Eerste",
				"sPrevious": "Vorige",
				"sNext": "Volgende",
				"sLast": "Laatste"}
		},
		//"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/communicatie/bibliotheek/catalogusdata",
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "sEigenaarFilter", "value": $('span.filter.actief').attr('id') } );
			aoData.push( { "name": "sView", "value": $('input[name=boekstatus]').is(':checked') } );
		},
		"iDisplayLength": 30,
		"bInfo": false,
		"bLengthChange": false,
		"bStateSave": true,
		"iCookieDuration": 60*15, // 15 min
		"fnStateSaveCallback": function ( oSettings, sValue ) {
			sValue += ',"sEigenaarFilter": "'+$('span.filter.actief').attr('id')+'"';
			sValue += ',"sView": '+$('input[name=boekstatus]').is(':checked');
			return sValue;
		},
		"fnStateLoadCallback": function ( oSettings, oData ) {
			var aEigenaarfilters = [ "alle", "csr", "leden", "eigen", "geleend"];
			if($.inArray(oData.sEigenaarFilter, aEigenaarfilters) == -1) {
				oData.sEigenaarFilter = 'csr';
			}
			$('span.filter').removeClass('actief').addClass('button');
			$('span.filter#'+oData.sEigenaarFilter).removeClass('button').addClass('actief');

			$('input[name=boekstatus]').attr('checked', oData.sView);
			return true;
		},
		"aaSorting": [[0, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": getKolommen()
	});

	function getKolommen() {
		var kolommen;
		if($("#boekencatalogus").hasClass("lid")){
			kolommen = {"aoKolommen": [
				{'sType': 'html'}, // titel
				{'sType': 'html'}, // auteur
				{'sType': 'html'}, // rubriek
				{'sType': 'html',"sWidth": "40px"},
				{'sType': 'html',"sWidth": "125px", "bVisible": false}, // eigenaar
				{'sType': 'html',"sWidth": "125px", "bVisible": false}, //uitgeleend aan
				{'sType': 'html',"sWidth": "100px", "bVisible": false} // uitleendatum
			]};
		}else{
			kolommen = {"aoKolommen": [
				{'sType': 'html',"sWidth": "400px"}, // titel
				{'sType': 'html'}, // auteur
				{'sType': 'html',"sWidth": "300px"} // rubriek
			]};
		}
		return kolommen.aoKolommen;
	}

	//catalogus: update de tabel bij kiezen van een filteroptie
	$('span.filter').click( function() { 
		//opmaak van knoppen aanpassen
		$('span.filter').removeClass('actief').addClass('button');
		$(this).removeClass('button').addClass('actief');
		//actie
		oTableCatalogus.fnDraw(); 
	});
	//catalogus: update de tabel als 'eigenaar&lener' wordt aangevinkt
	$('input#boekstatus').click( function() { 
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var oTable = $('#boekencatalogus').dataTable();
	
		var bVis = $('input[name=boekstatus]').is(':checked');
		oTable.fnSetColumnVis( 6, bVis, false);
		oTable.fnSetColumnVis( 5, bVis, false);
		oTable.fnSetColumnVis( 4, bVis, true );
	 } );

	// boekpagina: vult code-veld
	biebCodeVakvuller();

	// boekpagina: 
	//   Suggesties voor zoekveld uit Google books. 
	//   Kiezen van een suggestie plaatst in alle velden de juiste info.

	//suggestiemenu configureren
	$("#boekzoeker").autocomplete("https://www.googleapis.com/books/v1/volumes",{
		dataType: 'jsonp',
		parse: function(data) {
			var rows = new Array();
			data = data.items;
			for(var i=0; i<data.length; i++){
				var datarow = data[i].volumeInfo;
				rows[i] = { data:datarow, value:datarow.title, result: datarow.title+' '+(datarow.authors ? datarow.authors.join(', ') : '') };
			}
			return rows;
		},
		formatItem: function(row, i, n) {
			return row.title+'<br /><i>'+(row.authors ? row.authors.join(', ') : '')+'</i>';
		},
		formatResult: function(row) {
			return row.title+' '+(row.authors ? row.authors.join(', ') : '');
		},
		extraParams: {
			limit: '',
			fields: 'items(volumeInfo(authors,industryIdentifiers,language,pageCount,publishedDate,publisher,title))',
			key: 'AIzaSyC7zu4-25xbizddFWuIbn107WTTPr37jos',
			maxResults: 25,
			qq: 1
		},
		minChars: 7,
		delay: 1000,
		max: 25

	//invullen van info van gekozen suggestie in de boekvelden
	}).result(function(event, datarow, formatted) {
		var isbn = '';
		if(datarow.industryIdentifiers[1] && datarow.industryIdentifiers[1].type == "ISBN_13"){
			isbn = datarow.industryIdentifiers[1].identifier;
		}
		var lang = {
			nl: "Nederlands", 	en: "Engels", 	fr: "Frans",
			de: "Duits", 		bg: "Bulgaars", es: "Spaans",
			cs: "Tsjechisch", 	da: "Deens", 	et: "Ests",
			el: "Grieks", 		ga: "Iers", 	it: "Italiaans",
			lv: "Lets", 		lt: "Litouws", 	hu: "Hongaars",
			mt: "Maltees", 		pl: "Pools", 	pt: "Portugees",
			ro: "Roemeens", 	sk: "Slowaaks", sl: "Sloveens",
			fi: "Fins", 		sv: "Zweeds"
		};
		//gegevens in invulvelden plaatsen
		$("#field_titel").val(datarow.title);
		$("#field_auteur").val((datarow.authors ? datarow.authors.join(', ') : ''));
		$("#field_paginas").val(datarow.pageCount);
		$("#field_taal").val(lang[datarow.language] ? lang[datarow.language] : datarow.language);
		$("#field_isbn").val(isbn);
		$("#field_uitgeverij").val(datarow.publisher);
		$("#field_uitgavejaar").val(datarow.publishedDate ? datarow.publishedDate.substring(0,4) : '');

	//kleurt invoerveld rood bij te korte zoekterm
	}).keyup(function(event){
		var inputl = $(this).val().length
		if(inputl>0 && inputl < 7){
			$(this).css("background-color","#ffded1");
		}else{
			$(this).css("background-color","white");
		}
	});

	//boekpagina: autocomplete voor bewerkvelden uit C.S.R.-database. 
	// TODO naar standaard formuliervelden opzetten.
	var options = {
		dataType: 'json',
		parse: function(data) {
			var rows = new Array();
			for(var i=0; i<data.length; i++){
				var datarow = data[i];
				rows[i] = { data:datarow, value:datarow, result: datarow };
			}
			return rows;
		},
		formatItem: function(row, i, n) {
			return row;
		},
		clickFire: true, 
		max: 20
	};
	function opslaanGekozenWaarde(event, datarow, formatted){
		var ID = jQuery(this).attr('id').substring(6);
		var waarde = datarow;
		saveChange(ID,waarde);
	};
	$("#field_titel").autocomplete("/communicatie/bibliotheek/autocomplete/titel",options);
	$(".bewerk #field_titel").result(opslaanGekozenWaarde);
	$("#field_auteur").autocomplete("/communicatie/bibliotheek/autocomplete/auteur",options);
	$(".bewerk #field_auteur").result(opslaanGekozenWaarde);
	$("#field_taal").autocomplete("/communicatie/bibliotheek/autocomplete/taal",options);
	$(".bewerk #field_taal").result(opslaanGekozenWaarde);
	$("#field_uitgeverij").autocomplete("/communicatie/bibliotheek/autocomplete/uitgeverij",options);
	$(".bewerk #field_uitgeverij").result(opslaanGekozenWaarde);

	//boekpagina: opmerkingveld aan auteurveld toevoegen
	$("#auteur").append('<div class="suggestieveld suggestie">Achternaam, Voornaam L. van</div>');

	//boekpagina: meldingsvelden toevoegen bewerkbare velden
	$('.blok .veld').append('<div class="melding"></div>');
	//boekpagina: asynchroon opslaan toevoegen
	$('.blok .veld input,.blok .veld select').each(function(index, input){
		$(this).after(
			//opslaan-knop toevoegen, met event die met ajax input opslaat
			$('<div class="knop opslaan">Opslaan</div>').mousedown(function(){
				var fieldname = input.id.substring(6);
				var waarde=$(this).prev().val();
				var boekid=jQuery(".boek").attr('id');
				var dataString='id='+ fieldname +'&'+ fieldname +'='+ waarde;
				jQuery.ajax({
					type: "POST",
					url: '/communicatie/bibliotheek/bewerkboek/'+ boekid,
					data: dataString,
					cache: false,
					dataType: "json",
					success: function(result){
						var field = $("#"+fieldname);
						if(result.success){
							//opgeslagen waarde in input zetten
							$("#"+input.id).val(result.value);
							//tijdelijke groene bevestiging
							field.removeClass('metfouten').addClass('opgeslagen');
							window.setTimeout(function(){
								field.removeClass('opgeslagen');
								//field.find(".melding").fadeOut();
							}, 3000);
						}else{
							//rode foutmelding
							field.removeClass('opgeslagen').addClass('metfouten');
						}
						field.find(".melding").html(result.melding).show();
						//verwijder bewerkt-markering
						$("#"+input.id).removeClass('nonsavededits')
					}
				});
			})
		).keydown(function(){
			//bewerkte velden markeren
			$(this).addClass('nonsavededits');
		}).change(function(){
			//lege velden krijgen een border
			if($(this).val().length==0){
				$(this).addClass("leeg");
			}else{
				$(this).removeClass("leeg");
			}
		}).change();
	}); 

});

//geneert biebcode met waardes uit andere velden
function biebCodeVakvuller(){
	jQuery(".knop.genereer").click(function (event) {
		event.preventDefault();
		jQuery("#field_code").val(
			jQuery("#field_rubriek").val() + '.' + jQuery("#field_auteur").val().substring(0,3).toLowerCase()
		);
		jQuery("#field_code").trigger('change');
	});
}
//zoekt naam op
function naamCheck(fieldname){
	field=document.getElementById('field_'+fieldname);
	if(field.value.length>2){
		http.abort();
		http.open("GET", "/tools/naamlink.php?naam="+field.value, true);
		http.onreadystatechange=function(){
			if(http.readyState == 4){
				document.getElementById('preview_'+fieldname).innerHTML=http.responseText;
			}
		}
		http.send(null);
	}else{
		document.getElementById('preview_'+fieldname).innerHTML='';
	}
	return null;
}
