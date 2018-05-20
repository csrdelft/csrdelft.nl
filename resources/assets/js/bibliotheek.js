import $ from 'jquery';
import Bloodhound from 'typeahead.js';

import {initHoverIntents} from './context';

/*
 *	Bibliotheekjavascriptcode.
 */
$(function ($) {
	/*********************************************
	 * Catalogus
	 *********************************************/
	//catalogus: tabellen naar zebra converteren.
	$('#boeken').find('tr:odd').addClass('odd');

    function getKolommen() {
        let kolommen;

        if ($('#boekencatalogus').hasClass('lid')) {
            kolommen = {'aoKolommen': [
                    {'sType': 'html'}, // titel
                    {'sType': 'html'}, // auteur
                    {'sType': 'html'}, // rubriek
                    {'sType': 'html', 'sWidth': '40px'},
                    {'sType': 'html', 'sWidth': '125px', 'bVisible': false}, // eigenaar
                    {'sType': 'html', 'sWidth': '125px', 'bVisible': false}, //uitgeleend aan
                    {'sType': 'html', 'sWidth': '100px', 'bVisible': false} // uitleendatum
                ]};
        } else {
            kolommen = {'aoKolommen': [
                    {'sType': 'html', 'sWidth': '400px'}, // titel
                    {'sType': 'html'}, // auteur
                    {'sType': 'html', 'sWidth': '300px'} // rubriek
                ]};
        }
        return kolommen.aoKolommen;
    }

	//catalogus: hippe sorteerbare tabel fixen.
	let oTableCatalogus = $('#boekencatalogus').dataTable({
		'oLanguage': {
			'sZeroRecords': 'Geen boeken gevonden',
			'sInfoEmtpy': 'Geen boeken gevonden',
			'sSearch': 'Zoeken:',
			oPaginate: {
				'sFirst': 'Eerste',
				'sPrevious': 'Vorige',
				'sNext': 'Volgende',
				'sLast': 'Laatste'}
		},
		//"bProcessing": true,
		'bServerSide': true,
		'sAjaxSource': '/bibliotheek/catalogusdata',
		'fnServerParams': (aoData) => {
			aoData.push({'name': 'sEigenaarFilter', 'value': $('span.filter.actief').attr('id')});
			aoData.push({'name': 'sView', 'value': $('input[name=boekstatus]').is(':checked')});
		},
		'iDisplayLength': 30,
		'bInfo': false,
		'bLengthChange': false,
		'bStateSave': true,
		'iCookieDuration': 60 * 15, // 15 min
		'fnStateSaveCallback': (oSettings, sValue) => {
            let eigenaarFilter = $('span.filter.actief').attr('id'),
                checked = $('input[name=boekstatus]').is(':checked');
            sValue += `,"sEigenaarFilter": "${eigenaarFilter}","sView": ${checked}`;
			initHoverIntents();
			return sValue;
		},
		'fnStateLoadCallback': (oSettings) => {
			let oData = oSettings.aoData;
			let aEigenaarfilters = ['alle', 'csr', 'leden', 'eigen', 'geleend'];
			if ($.inArray(oData.sEigenaarFilter, aEigenaarfilters) === -1) {
				oData.sEigenaarFilter = 'csr';
			}
			$('span.filter').removeClass('actief').addClass('button');
			$('span.filter#' + oData.sEigenaarFilter).removeClass('button').addClass('actief');

			$('input[name=boekstatus]').attr('checked', oData.sView);
			return true;
		},
		'aaSorting': [[0, 'asc']],
		'sPaginationType': 'full_numbers',
		'aoColumns': getKolommen()
	});

	//catalogus: update de tabel bij kiezen van een filteroptie
	$('span.filter').click(function () {
		//opmaak van knoppen aanpassen
		$('span.filter').removeClass('actief').addClass('button');
		$(this).removeClass('button').addClass('actief');
		//actie
		oTableCatalogus.fnDraw(); // TODO: Gebruik datatables api
	});

	//catalogus: update de tabel als 'eigenaar&lener' wordt aangevinkt
	$('input#boekstatus').click(function () {
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		let oTable = $('#boekencatalogus').dataTable();

		let bVis = $('input[name=boekstatus]').is(':checked');
		oTable.fnSetColumnVis(6, bVis, false); // TODO: fnSetColumnVis bestaat niet meer
		oTable.fnSetColumnVis(5, bVis, false);
		oTable.fnSetColumnVis(4, bVis, true);
	});



	/************************************************
	 * Boekpagina
	 ************************************************/
	// boekpagina: vult code-veld
    //voeg 'genereer'-knop toe aan codefield, die een biebcode geneert met waardes uit andere velden
    function biebCodeVakvuller() {
        let codeveld = $('input[name=code]');
        let codeknop = $('<a class="btn genereer" title="Biebcode invullen">Genereer</a>').mousedown(function (event) {
            event.preventDefault();
            codeveld.val(
                $('select[name=rubriek]').val() + '.' + $('input[name=auteur]').val().substring(0, 3).toLowerCase()
            ).focus();
        });
        codeveld.after(codeknop);
    }
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
		let isbn = '';

		if (datarow.industryIdentifiers && datarow.industryIdentifiers[1] && datarow.industryIdentifiers[1].type === 'ISBN_13') {
			isbn = datarow.industryIdentifiers[1].identifier;
		}

		return isbn;
	}
	function getLanguage(datarow) {
		let lang = {
			nl: 'Nederlands', en: 'Engels', fr: 'Frans',
			de: 'Duits', bg: 'Bulgaars', es: 'Spaans',
			cs: 'Tsjechisch', da: 'Deens', et: 'Ests',
			el: 'Grieks', ga: 'Iers', it: 'Italiaans',
			lv: 'Lets', lt: 'Litouws', hu: 'Hongaars',
			mt: 'Maltees', pl: 'Pools', pt: 'Portugees',
			ro: 'Roemeens', sk: 'Slowaaks', sl: 'Sloveens',
			fi: 'Fins', sv: 'Zweeds'
		};

		return lang[datarow.language] ? lang[datarow.language] : datarow.language;
	}

	//suggestiemenu configureren
	let boekenSource = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.whitespace,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		limit: 25,
		remote: {
			url: 'https://www.googleapis.com/books/v1/volumes?q=%QUERY',
			filter: (data) => {
				let rows = [];
				data = data.items;
				for (let i = 0; i < data.length; i++) {
					let datarow = data[i].volumeInfo;
					datarow.index = i;
					rows[i] = datarow;
				}
				return rows;
			},
			ajax: {
				data: {
					fields: 'items(volumeInfo(authors,industryIdentifiers,language,pageCount,publishedDate,publisher,title))',
					key: 'AIzaSyC7zu4-25xbizddFWuIbn107WTTPr37jos'
				}
			}
		}
	});
	boekenSource.initialize();
	$('#boekzoeker').typeahead({
		autoselect: true,
		hint: true,
		highlight: true,
		minLength: 7
	}, {
		name: 'boekenSource',
		displayKey: 'title',
		source: boekenSource.ttAdapter(),
		templates: {
			header: '<h3>Boeken</h3>',
			suggestion: (row) => {
				let item = `<div style="margin: 5px 10px" title="Titel: ${row.title} | Auteur: ${getAuteur(row)} | Pagina's: ${row.pageCount} | Taal: ${getLanguage(row)} | ISBN: ${getIsbn(row)} | Uitgeverij: ${row.publisher} | Uitgavejaar: ${getPublishedDate(row)}">`;
				item += `<span class="dikgedrukt">${row.title}</span><br /><span class="cursief">${getAuteur(row)}</span>`;
				item += '</div>';
				return item;
			}
		}
	}).keyup(function () {
		let inputlen = $(this).val().length;

		if (inputlen > 0 && inputlen < 7) {
			$(this).css('background-color', '#ffcc96');
		} else {
			$(this).css('background-color', 'white');
		}
	}).on('typeahead:selected', function(event, row) {
		//gegevens in invulvelden plaatsen
		let values = [
			{key: 'titel', value: row.title},
			{key: 'auteur', value: getAuteur(row)},
			{key: 'paginas', value: row.pageCount},
			{key: 'taal', value: getLanguage(row)},
			{key: 'isbn', value: getIsbn(row)},
			{key: 'uitgeverij', value: row.publisher},
			{key: 'uitgavejaar', value: getPublishedDate(row)}
		];
		values.forEach(function(el) {
			$(`input[name=${el.key}]`).val(el.value);
		});
	});

	//boekpagina: autocomplete voor bewerkvelden uit C.S.R.-database.
	/* result = array(
	 *		array(data:array(..,..,..), value: "string", result:"string"),
	 * 		array(... )
	 * )
	 * formatItem geneert html-items voor de suggestielijst, afstemmen op data-array
	 */
	let bestaandeBoekenSource = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.whitespace,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		limit: 20,
		remote: 'autocomplete/titel?q=%QUERY'
	});
	bestaandeBoekenSource.initialize();
	$('form.Formulier input:not(.tt-hint):first').typeahead({
		autoselect: true,
		hint: true,
		highlight: true
	}, {
		name: 'bestaandeBoekenSource',
		displayKey: 'value',
		source: bestaandeBoekenSource.ttAdapter(),
		templates: {
			header: '<h3>Bestaande Boeken</h3>',
			suggestion: function (row) {
				return `<div style="margin: 5px 10px">Ga naar: <a href="/bibliotheek/boek/${row.data.id}" target="_blank">${row.data.titel}</a></div>`;
			}
		}
	}).on('typeahead:select', function (event, row) {
		window.open(`/bibliotheek/boek/${row.data.id}`);
	});

	//boekpagina: asynchroon opslaan toevoegen
	//opslaan-knop toevoegen, met event die met ajax de veldwaarde opslaat
	$('.blok .InputField input,.blok .InputField textarea,.blok .InputField select').each(function (index, input) {
		$(this).after('<div class="melding"></div>').after($('<a class="btn opslaan">Opslaan</a>').mousedown(function () {
			let boekid = $('.boek').attr('id'),
				$input = $(`#${input.id}`),
				fieldname = $input.attr('name'),
				waarde = $input.val(),
				dataString = `id=${fieldname}&${fieldname}=${waarde}`;

			$.ajax({
				type: 'POST',
				url: `/bibliotheek/bewerkboek/${boekid}`,
				data: dataString,
				cache: false,
				dataType: 'json',
				success: (result) => {
					let field = $(`#${input.id.substring(6)}`),
						$inputelem = $(`#${input.id}`);

					if (result.value) {
						//opgeslagen waarde in input zetten en een tijdelijke succesmelding
						$inputelem.val(result.value);
						field.removeClass('metFouten').addClass('opgeslagen');
						window.setTimeout(function () {
							field.removeClass('opgeslagen');
						}, 3000);
						//bij boek uitlenen pagina herladen
						if (input.id.substring(6, 11) === 'lener') {
							location.reload();
						}
					} else {
						//rode foutmelding
						field.removeClass('opgeslagen').addClass('metFouten');
					}
					//meldingsboodschap plaatsen, en verwijder bewerkt-markering
					field.find('.melding').html(result.melding).show();
					$inputelem.removeClass('nonsavededits');
				}
			});
		})
				).keydown(function () {
			//bewerkte velden markeren
			$(this).addClass('nonsavededits');
		}).change(function () {
			//lege velden krijgen een border
			if ($(this).val().length === 0) {
				$(this).addClass('leeg');
			} else {
				$(this).removeClass('leeg');
			}
		}).change();
	});
});

