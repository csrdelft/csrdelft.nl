import axios from 'axios';
import {domUpdate} from './domUpdate';
import {isLoggedIn} from './util';

// ----------------------------------------------------------------------------
// markItUp!
// ----------------------------------------------------------------------------
// Copyright (C) 2008 Jay Salvat
// http://markitup.jaysalvat.com/
// ----------------------------------------------------------------------------
// BBCode tags example
// http://en.wikipedia.org/wiki/Bbcode
// ----------------------------------------------------------------------------
// Feel free to add more tags
// ----------------------------------------------------------------------------
export const bbCodeSet = {
	markupSet: [
		{className: 'ico text_bold', name: 'Dikgedrukt', key: 'B', openWith: '[b]', closeWith: '[/b]'},
		{className: 'ico text_italic', name: 'Cursief', key: 'I', openWith: '[i]', closeWith: '[/i]'},
		{className: 'ico text_underline', name: 'Onderstreept', key: 'U', openWith: '[u]', closeWith: '[/u]'},
		{className: 'ico text_strikethrough', name: 'Doorgestreept', key: 'S', openWith: '[s]', closeWith: '[/s]'},
		{separator: '|'},
		{className: 'ico text_smallcaps', name: 'Offtopic', key: 'O', openWith: '[offtopic]', closeWith: '[/offtopic]'},
		{
			className: 'ico comments',
			closeWith: '[/citaat]',
			key: 'Q',
			name: 'Citaat',
			openWith: '[citaat=Naam_of_lidnummer]',
		},
		{separator: '|'},
		{
			className: 'ico link',
			closeWith: '[/url]',
			key: 'L',
			name: 'Link',
			openWith: '[url=[![Url]!]]',
			placeHolder: 'Link tekst',
		},
		{
			className: 'ico email_link',
			closeWith: '[/email]',
			key: 'E',
			name: 'Email',
			openWith: '[email=[![Email adres]!]]',
			placeHolder: 'Link tekst',
		},
		{separator: '|'},
		{
			className: 'ico photos', name: 'Fotoalbum', replaceWith: (markitup: { selection: unknown }): unknown => {
				let url = window.prompt('Url', '');
				if (url) {
					url = decodeURIComponent(url.trim());
					const pos = url.indexOf('/fotoalbum/');
					if (pos > 0) {
						url = url.substring(pos + 10);
						return '[fotoalbum]' + url + '[/fotoalbum]';
					}
					alert('Ongeldige url!');
				}
				return markitup.selection;
			},
		},
		{
			className: 'ico photo',
			name: 'Poster of foto uit album',
			replaceWith: (markitup: { selection: unknown }): unknown => {
				let url = window.prompt('Url', '');
				if (url) {
					url = decodeURIComponent(url.trim());
					const pos = url.indexOf('/fotoalbum/');
					if (pos > 0) {
						url = url.substring(pos + 10).replace('_resized/', '').replace('_thumbs/', '').replace('#', '');
						return '[foto]' + url + '[/foto]';
					}
					alert('Ongeldige url!');
				}
				return markitup.selection;
			},
		},
		(isLoggedIn() ?
			{
				className: 'ico picture', name: 'Plaatje',
				closeWith: () => {
					axios.get('/forum/plaatjes/upload').then((response) => {
						domUpdate(response.data);
					});
					return '';
				},
			} :
			// Hide this (maybe temporarily) for registered users to encourage uploading images to de stek
			{className: 'ico picture', name: 'Afbeelding', replaceWith: '[img][![Url]!][/img]'}),
		{className: 'ico film', name: 'Video', replaceWith: '[video][![Url]!][/video]'},
		{separator: '|'},
		{className: 'ico map', name: 'Kaart', openWith: '[locatie]', closeWith: '[/locatie]', placeHolder: 'C.S.R. Delft'},
		{className: 'ico sound_mute', name: 'Verklapper', openWith: '[verklapper]', closeWith: '[/verklapper]'},
		{
			className: 'ico shield',
			closeWith: '[/prive]',
			name: 'Privé',
			openWith: '[prive]',
			placeHolder: 'Afgeschermde gegevens',
		},
		// {className: 'btn-kop', name: 'Kop',
		// 	dropMenu: [
		// 		{className: 'btn-h1', name: 'H1', openWith: '[h=1]', closeWith: '[/h]'},
		// 		{className: 'btn-h2', name: 'H2', openWith: '[h=2]', closeWith: '[/h]'},
		// 		{className: 'btn-h3', name: 'H3', openWith: '[h=3]', closeWith: '[/h]'},
		// 		{className: 'btn-h4', name: 'H4', openWith: '[h=4]', closeWith: '[/h]'},
		// 		{className: 'btn-h5', name: 'H5', openWith: '[h=5]', closeWith: '[/h]'},
		// 		{className: 'btn-h6', name: 'H6', openWith: '[h=6]', closeWith: '[/h]'}
		// 	]},
		// {separator: '|'},
		// {className: 'btn-lijst-1', name: 'Genummerde lijst',
		// openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]'},
		// {className: 'btn-lijst-a', name: 'Ongenummerde lijst', openWith: '[list]\n', closeWith: '\n[/list]'},
		// {className: 'btn-lijst-punt', name: 'Lijstpunt', openWith: '[*] '},
		{separator: '|'},
		{className: 'ico script_code_red', name: 'Code', openWith: '[code]', closeWith: '[/code]'},
		{className: 'ico tag', name: 'Opmaakcode tonen', openWith: '[tekst]', closeWith: '[/tekst]'},
		// {className: 'btn-preview', name: 'Voorbeeld', call: 'preview'}
	],
	nameSpace: 'CsrBB',
	previewParserPath: '/tools/bbcode', // path to your BBCode parser
};
