import axios from 'axios';
import Bloodhound from 'corejs-typeahead';
import $ from 'jquery';

interface GoogleBookResponse {
	volumeInfo: GoogleBook;
}

interface GoogleBook {
	index: number;
	title: string;
	pageCount: number;
	authors: string[];
	publisher: string;
	publishedDate: string;
	industryIdentifiers: GoogleIndustryIdentifier[];
	language: string;
}

interface GoogleIndustryIdentifier {
	type: string;
	identifier: string;
}

/*
 *	Bibliotheekjavascriptcode.
 */
$(() => {
	/************************************************
	 * Boekpagina
	 ************************************************/
	// boekpagina: vult code-veld
	// voeg 'genereer'-knop toe aan codefield, die een biebcode geneert met waardes uit andere velden
	function biebCodeVakvuller() {
		const codeveld = $('input[name=code]');
		const codeknop = $(
			'<a class="btn genereer" title="Biebcode invullen">Genereer</a>'
		);
		codeknop.on('mousedown', (event) => {
			event.preventDefault();
			const categorieId = $('select[name=categorie_id]').val();
			const auteur = ($('input[name=auteur]').val() as string)
				.substring(0, 3)
				.toLowerCase();
			codeveld.val(categorieId + '.' + auteur).trigger('focus');
		});
		codeveld.after(codeknop);
	}

	biebCodeVakvuller();

	// boekpagina:
	//   Suggesties uit Google books.
	//   Kiezen van een suggestie plaatst in alle velden de juiste info.
	function getAuteur(datarow: GoogleBook) {
		return datarow.authors ? datarow.authors.join(', ') : '';
	}

	function getPublishedDate(datarow: GoogleBook) {
		return datarow.publishedDate ? datarow.publishedDate.substring(0, 4) : '';
	}

	function getIsbn(datarow: GoogleBook) {
		let isbn = '';

		if (
			datarow.industryIdentifiers &&
			datarow.industryIdentifiers[1] &&
			datarow.industryIdentifiers[1].type === 'ISBN_13'
		) {
			isbn = datarow.industryIdentifiers[1].identifier;
		}

		return isbn;
	}

	function getLanguage(datarow: GoogleBook) {
		const lang = {
			nl: 'Nederlands',
			en: 'Engels',
			fr: 'Frans',
			de: 'Duits',
			bg: 'Bulgaars',
			es: 'Spaans',
			cs: 'Tsjechisch',
			da: 'Deens',
			et: 'Ests',
			el: 'Grieks',
			ga: 'Iers',
			it: 'Italiaans',
			lv: 'Lets',
			lt: 'Litouws',
			hu: 'Hongaars',
			mt: 'Maltees',
			pl: 'Pools',
			pt: 'Portugees',
			ro: 'Roemeens',
			sk: 'Slowaaks',
			sl: 'Sloveens',
			fi: 'Fins',
			sv: 'Zweeds',
		};

		return lang[datarow.language] ? lang[datarow.language] : datarow.language;
	}

	// suggestiemenu configureren
	const boekenSource = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.whitespace,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: 'https://www.googleapis.com/books/v1/volumes?q=%QUERY',
			wildcard: '%QUERY',
			transform: (response) => {
				const rows = [];
				const data = response.data.items as GoogleBookResponse[];
				for (let i = 0; i < data.length; i++) {
					const datarow = data[i].volumeInfo;
					datarow.index = i;
					rows[i] = datarow;
				}
				return rows;
			},
			transport: (options, onSuccess, onError) => {
				axios
					.request({
						params: {
							fields:
								'items(volumeInfo(authors,industryIdentifiers,language,pageCount,publishedDate,publisher,title))',
							key: 'AIzaSyC7zu4-25xbizddFWuIbn107WTTPr37jos',
						},
						...options,
					})
					.then(onSuccess)
					.catch(onError);
			},
		},
	});
	boekenSource.initialize();
	$('#boekzoeker')
		.typeahead(
			{
				autoselect: true,
				hint: true,
				highlight: true,
				minLength: 4,
			},
			{
				name: 'boekenSource',
				displayKey: 'title',
				source: boekenSource.ttAdapter(),
				templates: {
					header: '<h3>Boeken</h3>',
					suggestion: (row: GoogleBook) => {
						const title = `Titel: ${row.title}
				| Auteur: ${getAuteur(row)}
				| Pagina's: ${row.pageCount}
				| Taal: ${getLanguage(row)}
				| ISBN: ${getIsbn(row)}
				| Uitgeverij: ${row.publisher}
				| Uitgavejaar: ${getPublishedDate(row)}`;
						return `
<div style="margin: 5px 10px" title="${title}">
	<span class="dikgedrukt">${
		row.title
	}</span><br /><span class="cursief">${getAuteur(row)}</span>
</div>`;
					},
				},
			}
		)
		.on('keyup', function () {
			const inputlen = ($(this).val() as string).length;

			if (inputlen > 0 && inputlen < 7) {
				$(this).css('background-color', '#ffcc96');
			} else {
				$(this).css('background-color', 'white');
			}
		})
		.on('typeahead:selected', (event, row) => {
			// gegevens in invulvelden plaatsen
			const values = [
				{ key: 'titel', value: row.title },
				{ key: 'auteur', value: getAuteur(row) },
				{ key: 'paginas', value: row.pageCount },
				{ key: 'taal', value: getLanguage(row) },
				{ key: 'isbn', value: getIsbn(row) },
				{ key: 'uitgeverij', value: row.publisher },
				{ key: 'uitgavejaar', value: getPublishedDate(row) },
			];
			values.forEach((el) => {
				$(`input[name=${el.key}]`).val(el.value);
			});
		});

	// boekpagina: autocomplete voor bewerkvelden uit C.S.R.-database.
	const bestaandeBoekenSource = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.whitespace,
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: '/bibliotheek/autocomplete/titel?q=%QUERY',
			wildcard: '%QUERY',
		},
	});
	bestaandeBoekenSource.initialize();

	$('form.Formulier input.TitelField').typeahead(
		{
			hint: true,
			highlight: true,
			minLength: 1,
		},
		{
			name: 'bestaandeBoekenSource',
			displayKey: 'value',
			source: bestaandeBoekenSource.ttAdapter(),
			templates: {
				header: '<h3>Bestaande Boeken</h3>',
				suggestion(row) {
					return `<div style="margin: 5px 10px">Ga naar:
 <a href="/bibliotheek/boek/${row.id}" target="_blank">${row.value}</a></div>`;
				},
			},
		}
	);
});
