/*
 *	Bibliotheekjavascriptcode.
 */
jQuery(document).ready(function ($) {
	/*********************************************
	 * Catalogus
	 *********************************************/
	//catalogus: tabellen naar zebra converteren.
	jQuery('#boeken').find('tr:odd').addClass('odd');


	//catalogus: hippe sorteerbare tabel fixen.
	var oTableCatalogus = jQuery("#boekencatalogus").dataTable({
		"oLanguage": {
			"sZeroRecords": "Geen boeken gevonden",
			"sInfoEmtpy": "Geen boeken gevonden",
			"sSearch": "Zoeken:",
			oPaginate: {
				"sFirst": "Eerste",
				"sPrevious": "Vorige",
				"sNext": "Volgende",
				"sLast": "Laatste"}
		},
		//"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/bibliotheek/catalogusdata",
		"fnServerParams": function (aoData) {
			aoData.push({"name": "sEigenaarFilter", "value": $('span.filter.actief').attr('id')});
			aoData.push({"name": "sView", "value": $('input[name=boekstatus]').is(':checked')});
		},
		"iDisplayLength": 30,
		"bInfo": false,
		"bLengthChange": false,
		"bStateSave": true,
		"iCookieDuration": 60 * 15, // 15 min
		"fnStateSaveCallback": function (oSettings, sValue) {
			sValue += ',"sEigenaarFilter": "' + $('span.filter.actief').attr('id') + '"';
			sValue += ',"sView": ' + $('input[name=boekstatus]').is(':checked');
			init_hoverIntents();
			return sValue;
		},
		"fnStateLoadCallback": function (oSettings) {
			var oData = oSettings.aoData;
			var aEigenaarfilters = ["alle", "csr", "leden", "eigen", "geleend"];
			if ($.inArray(oData.sEigenaarFilter, aEigenaarfilters) == -1) {
				oData.sEigenaarFilter = 'csr';
			}
			$('span.filter').removeClass('actief').addClass('button');
			$('span.filter#' + oData.sEigenaarFilter).removeClass('button').addClass('actief');

			$('input[name=boekstatus]').attr('checked', oData.sView);
			return true;
		},
		"aaSorting": [[0, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": getKolommen()
	});

	function getKolommen() {
		var kolommen;
		if ($("#boekencatalogus").hasClass("lid")) {
			kolommen = {"aoKolommen": [
					{'sType': 'html'}, // titel
					{'sType': 'html'}, // auteur
					{'sType': 'html'}, // rubriek
					{'sType': 'html', "sWidth": "40px"},
					{'sType': 'html', "sWidth": "125px", "bVisible": false}, // eigenaar
					{'sType': 'html', "sWidth": "125px", "bVisible": false}, //uitgeleend aan
					{'sType': 'html', "sWidth": "100px", "bVisible": false} // uitleendatum
				]};
		} else {
			kolommen = {"aoKolommen": [
					{'sType': 'html', "sWidth": "400px"}, // titel
					{'sType': 'html'}, // auteur
					{'sType': 'html', "sWidth": "300px"} // rubriek
				]};
		}
		return kolommen.aoKolommen;
	}

	//catalogus: update de tabel bij kiezen van een filteroptie
	$('span.filter').click(function () {
		//opmaak van knoppen aanpassen
		$('span.filter').removeClass('actief').addClass('button');
		$(this).removeClass('button').addClass('actief');
		//actie
		oTableCatalogus.fnDraw();
	});

	//catalogus: update de tabel als 'eigenaar&lener' wordt aangevinkt
	$('input#boekstatus').click(function () {
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var oTable = $('#boekencatalogus').dataTable();

		var bVis = $('input[name=boekstatus]').is(':checked');
		oTable.fnSetColumnVis(6, bVis, false);
		oTable.fnSetColumnVis(5, bVis, false);
		oTable.fnSetColumnVis(4, bVis, true);
	});



	/************************************************
	 * Boekpagina
	 ************************************************/
	// boekpagina: vult code-veld
	biebCodeVakvuller();

	// boekpagina:
	//   Suggesties uit Google books.
	//   Kiezen van een suggestie plaatst in alle velden de juiste info.
	function getAuteur(datarow) {
		return datarow.authors ? datarow.authors.join(', ') : '';
	}
	function getPublishedDate(datarow) {
		return datarow.publishedDate ? datarow.publishedDate.substring(0, 4) : '';
	}
	function getIsbn(datarow) {
		var isbn = '';
		if (datarow.industryIdentifiers && datarow.industryIdentifiers[1] && datarow.industryIdentifiers[1].type === 'ISBN_13') {
			isbn = datarow.industryIdentifiers[1].identifier;
		}
		return isbn;
	}
	function getLanguage(datarow) {
		var lang = {
			nl: "Nederlands", en: "Engels", fr: "Frans",
			de: "Duits", bg: "Bulgaars", es: "Spaans",
			cs: "Tsjechisch", da: "Deens", et: "Ests",
			el: "Grieks", ga: "Iers", it: "Italiaans",
			lv: "Lets", lt: "Litouws", hu: "Hongaars",
			mt: "Maltees", pl: "Pools", pt: "Portugees",
			ro: "Roemeens", sk: "Slowaaks", sl: "Sloveens",
			fi: "Fins", sv: "Zweeds"
		};
		return lang[datarow.language] ? lang[datarow.language] : datarow.language;
	}

	try {
		//suggestiemenu configureren
		$("#boekzoeker").autocomplete("https://www.googleapis.com/books/v1/volumes", {
			dataType: 'jsonp',
			parse: function (data) {
				var rows = [];
				data = data.items;
				for (var i = 0; i < data.length; i++) {
					var datarow = data[i].volumeInfo;
					rows[i] = {data: datarow, value: datarow.title, result: datarow.title + ' ' + getAuteur(datarow)};
				}
				return rows;
			},
			formatItem: function (row, i, n) {
				var item = '<div title="titel: ' + row.title + "\nAuteur: " + getAuteur(row) + "\nPagina's: " + row.pageCount + "\nTaal: " + getLanguage(row) + "\nISBN: " + getIsbn(row) + "\nUitgeverij: " + row.publisher + "\nUitgavejaar: " + getPublishedDate(row) + '">';
				item += row.title + '<br /><span class="cursief">' + getAuteur(row) + '</span>';
				item += '</div>';
				return item;
			},
			formatResult: function (row) {
				return row.title + ' ' + getAuteur(row);
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
		}).result(function (event, row, formatted) {
			//gegevens in invulvelden plaatsen
			$("#field_titel").val(row.title);
			$("#field_auteur").val(getAuteur(row));
			$("#field_paginas").val(row.pageCount);
			$("#field_taal").val(getLanguage(row));
			$("#field_isbn").val(getIsbn(row));
			$("#field_uitgeverij").val(row.publisher);
			$("#field_uitgavejaar").val(getPublishedDate(row));

			//kleurt invoerveld rood bij te korte zoekterm
		}).keyup(function (event) {
			var inputlen = $(this).val().length;
			if (inputlen > 0 && inputlen < 7) {
				$(this).css("background-color", "#ffcc96");
			} else {
				$(this).css("background-color", "white");
			}
		});
	}
	catch (err) {
		console.log(err);
		// Missing js file
	}

	//boekpagina: autocomplete voor bewerkvelden uit C.S.R.-database.
	/* result = array(
	 *		array(data:array(..,..,..), value: "string", result:"string"),
	 * 		array(... )
	 * )
	 * formatItem geneert html-items voor de suggestielijst, afstemmen op data-array
	 */
	var options = {
		dataType: 'json',
		parse: function (result) {
			return result;
		},
		formatItem: function (row, i, n) {
			return 'Ga naar: <a href="/bibliotheek/boek/' + row.id + '" target="_blank">' + row.titel + '</a>';
		},
		clickFire: true,
		cacheLength: 0,
		delay: 600,
		max: 20
	};

	try {
		$("#field_titel").autocomplete("/bibliotheek/autocomplete/titel", options).result(function (event, row) {
			window.open('/bibliotheek/boek/' + row.id)
		});
	} catch (err) {
		console.log(err);
		// Missing js file
	}

	//boekpagina: asynchroon opslaan toevoegen
	//opslaan-knop toevoegen, met event die met ajax de veldwaarde opslaat
	$('.blok .InputField input,.blok .InputField textarea,.blok .InputField select').each(function (index, input) {
		$(this).after('<div class="melding"></div>'
				).after(
				$('<a class="btn opslaan">Opslaan</a>').mousedown(function () {
			var boekid = jQuery(".boek").attr('id');
			var fieldname = $("#" + input.id).attr('name');
			var waarde = $("#" + input.id).val();
			var dataString = 'id=' + fieldname + '&' + fieldname + '=' + waarde;
			jQuery.ajax({
				type: "POST",
				url: '/bibliotheek/bewerkboek/' + boekid,
				data: dataString,
				cache: false,
				dataType: "json",
				success: function (result) {
					var field = $("#" + input.id.substring(6));
					var $inputelem = $("#" + input.id);
					if (result.value) {
						//opgeslagen waarde in input zetten en een tijdelijke succesmelding
						$inputelem.val(result.value);
						field.removeClass('metFouten').addClass('opgeslagen');
						window.setTimeout(function () {
							field.removeClass('opgeslagen');
						}, 3000);
						//bij boek uitlenen pagina herladen
						if (input.id.substring(6, 11) == 'lener') {
							location.reload();
						}
					} else {
						//rode foutmelding
						field.removeClass('opgeslagen').addClass('metFouten');
					}
					//meldingsboodschap plaatsen, en verwijder bewerkt-markering
					field.find(".melding").html(result.melding).show();
					$inputelem.removeClass('nonsavededits');
				}
			});
		})
				).keydown(function () {
			//bewerkte velden markeren
			$(this).addClass('nonsavededits');
		}).change(function () {
			//lege velden krijgen een border
			if ($(this).val().length == 0) {
				$(this).addClass("leeg");
			} else {
				$(this).removeClass("leeg");
			}
		}).change();
	});
});

//voeg 'genereer'-knop toe aan codefield, die een biebcode geneert met waardes uit andere velden
function biebCodeVakvuller() {
	var codeknop = $('<a class="btn genereer" title="Biebcode invullen">Genereer</a>').mousedown(function (event) {
		event.preventDefault();
		$("#field_code").val(
				$("#field_rubriek").val() + '.' + $("#field_auteur").val().substring(0, 3).toLowerCase()
				).focus();
	});
	$("#field_code").after(codeknop);
}

