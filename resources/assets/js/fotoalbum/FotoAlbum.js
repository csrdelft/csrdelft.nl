import $ from 'jquery';
import EventEmitter from 'events';
import {basename, dirname, redirect, reload, selectText} from '../util';

import 'jgallery/dist/js/jgallery';

class FotoAlbum extends EventEmitter {
	container = null;

	constructor(wrapper) {
		super();
		this.wrapper = wrapper;
		this.isLoggedIn = wrapper.data('isLoggedIn');
		this.magAanpassen = wrapper.data('magAanpassen');
		this.slideshowInterval = wrapper.data('slideshowInterval');
		this.root = wrapper.data('root');
		this.itemsJson = wrapper.data('fotos');

		wrapper.find('.gallery').jGallery({
			width: '100%',
			height: '897px',
			mode: 'standard',
			canChangeMode: true,
			swipeEvents: false,
			browserHistory: true,
			disabledOnIE8AndOlder: true,
			preloadAll: false,
			maxMobileWidth: 767,
			draggableZoomHideNavigationOnMobile: true,
			autostart: true,
			autostartAtAlbum: 1,
			canZoom: true,
			draggableZoom: true,
			zoomSize: 'fit',
			'zoomSize:': 'original',
			backgroundColor: 'fff',
			textColor: '193b61',
			thumbnails: true,
			thumbType: 'image',
			thumbWidth: 150,
			thumbHeight: 150,
			thumbWidthOnFullScreen: 150,
			thumbHeightOnFullScreen: 150,
			thumbnailsPosition: 'bottom',
			hideThumbnailsOnInit: false,
			canMinimalizeThumbnails: true,
			thumbnailsHideOnMobile: false,
			transition: 'moveToLeft_scaleUp',
			transitionBackward: 'moveToRight_scaleUp',
			transitionTimingFunction: 'cubic-bezier(0,1,1,1)',
			transitionDuration: '0.7s',
			transitionCols: '1',
			transitionRows: '1',
			title: this.isLoggedIn,
			titleExpanded: false,
			tooltips: true,
			tooltipZoom: 'Zoom',
			tooltipToggleThumbnails: 'Toggle thumbnails',
			tooltipSeeAllPhotos: 'Grid',
			tooltipSeeOtherAlbums: 'Toon sub-albums',
			tooltipSlideshow: 'Slideshow',
			slideshowInterval: this.slideshowInterval,
			slideshow: true,
			slideshowAutostart: false,
			slideshowRandom: false,
			slideshowCanRandom: true,
			tooltipRandom: 'Random',
			tooltipFullScreen: 'Full screen',
			tooltipClose: 'Close',
			canClose: false,
			afterLoadPhoto: () => {
				this.container = $('div.jgallery');
				if (this.isLoggedIn) {
					// dynamic context menu
					this.updateContextMenu();
				}
				const zoom = this.container.find('div.zoom-container');
				// if zoomed in
				if (zoom.attr('data-size') !== 'fit') {
					this.showFullRes();
				}
				this.emit('afterLoadPhoto');
			},
			items: this.itemsJson
		});
		this.container = $('div.jgallery');
		this.container.addClass('noselect');


		$(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange', () => {
			if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
				// Fullscreen gegaan, door ons knopje.
			} else if (this.container.hasClass('jgallery-full-screen')) {
				$('span.change-mode').trigger('click');
			}
		});

		if (this.isLoggedIn) {

			// user selectable foto url
			this.container.find('div.title').off().on('click', function () {
				selectText(this);
			}).addClass('select-text');

			// knopje map omhoog
			$('<span class="fa fa-level-up jgallery-btn jgallery-btn-small" tooltip="Open parent album"></span>').on('click', () => {
				const url = this.getUrl();
				let fullscreen = '';
				if (this.container.hasClass('jgallery-full-screen')) {
					fullscreen = '?fullscreen';
				}
				window.location.href = dirname(dirname(url)).replace('plaetjes/', '') + fullscreen;
			}).prependTo(this.container.find('div.icons'));

		}

		// knopje subalbums
		this.container.find('.fa-list-ul').removeClass('fa-list-ul').addClass('fa-folder-open-o');

		// zoom button
		$('span.resize.jgallery-btn').on('click', () => {
			const zoom = this.container.find('div.zoom-container');
			if (zoom.attr('data-size') !== 'fit') {
				this.showFullRes();
				// verberg tags tijdens zoomen
				$('div.fototag').addClass('verborgen');
			}
			else {
				$('div.fototag').removeClass('verborgen');
			}
		}).appendTo(this.container.find('div.icons'));

		// toggle full screen
		this.container.find('span.change-mode').on('click', () => this.toggleFullScreen()).appendTo(this.container.find('div.icons'));
		// fullscreen GET param
		if (window.location.href.indexOf('?fullscreen') > 0 && !this.container.hasClass('jgallery-full-screen')) {
			$('span.change-mode').trigger('click');
		}

		if (this.isLoggedIn) {
			// foto context menu
			this.container.find('div.zoom').contextMenu({
				menuSelector: '#contextMenu',
				menuSelected: function () {
				}
			});

		}
		// mode change album selector to last position
		this.container.find('div.icons .jgallery-btn.change-album').appendTo(this.container.find('div.icons'));
	}

	static requestFullscreen() {
		const docelem = $('.jgallery').get(0);
		if (FotoAlbum.requestFullscreen) {
			FotoAlbum.requestFullscreen();
		} else if (docelem.webkitRequestFullscreen) {
			docelem.webkitRequestFullscreen();
		} else if (docelem.mozRequestFullScreen) {
			docelem.mozRequestFullScreen();
		} else if (docelem.msRequestFullscreen) {
			docelem.msRequestFullscreen();
		}
	}

	static exitFullScreen() {
		if (document.exitFullscreen) {
			document.exitFullscreen();
		}
		else if (document.webkitExitFullscreen) {
			document.webkitExitFullscreen();
		}
		else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		}
		else if (document.msExitFullscreen) {
			document.msExitFullscreen();
		}
	}

	getFullUrl() {
		return decodeURI(this.container.find('div.nav-bottom div.title').html());
	}

	getUrl() {
		return this.getFullUrl().replace(this.root, '');
	}

	toggleFullScreen() {
		//moveTagDivs();
		if (this.container.hasClass('jgallery-full-screen')) {
			FotoAlbum.requestFullscreen();
		}
		else {
			FotoAlbum.exitFullScreen();
		}
	}

	showFullRes() {
		const zoom = this.container.find('div.zoom-container');
		const foto = zoom.find('img.active');
		const setDimensions = function (img) {
			if (zoom.attr('data-size') === 'original') {
				foto.css({
					'max-width': '',
					'max-height': '',
					'width': img.naturalWidth,
					'height': img.naturalHeight,
					'margin-left': -img.naturalWidth / 2,
					'margin-top': -img.naturalHeight / 2
				});
				foto.attr('data-width', img.naturalWidth);
				foto.attr('data-height', img.naturalHeight);
				$(window).trigger('resize');
			}
		};
		if (foto[0].src !== this.getFullUrl()) {
			foto[0].src = this.getFullUrl();
		}
		if (zoom.attr('data-size') === 'original') {
			setDimensions(foto[0]);
		}
		if (zoom.attr('data-size') === 'fill') {
			$('span.resize.jgallery-btn').removeClass('fa-search-minus').addClass('fa-search-plus');
		}
	}

	updateContextMenu() {
		const cm = $('#contextMenu').empty();
		const addCMI = function (item, divider) {
			if (divider) {
				$('<div class="dropdown-divider"></div>').appendTo(cm);
			}
			$(item).appendTo(cm);
			if (item.hasClass('disabled')) {
				item.parent().addClass('disabled');
			}
		};

		if (this.isLoggedIn) {
			// knopje downloaden
			const btnDown = $('<a id="btnDown" class="dropdown-item" tabindex="-1"><span class="fa fa-download"></span> &nbsp; Downloaden</a>');
			btnDown.on('click', () => window.location.href = `/fotoalbum/download${this.getUrl()}`);
			addCMI(btnDown);
		}
		if (this.isLoggedIn) {
			// knopje taggen
			const btnTag = $('<a id="btnTag" class="dropdown-item" tabindex="-1"><span class="fa fa-smile-o"></span> &nbsp; Leden etiketteren</a>');
			btnTag.on('click', () => this.container.find('span.fa-smile-o.jgallery-btn').click());
			addCMI(btnTag);
		}

		// knopje full screen
		const btnFS = $('<a id="btnFS" class="dropdown-item" tabindex="-1"><span class="fa"></span> &nbsp; Volledig scherm</a>');
		btnFS.on('click', () => {
			const btn = this.container.find('span.change-mode');
			btn.click();
			// sync state
			if (btn.hasClass('fa-expand')) {
				btnFS.find('span.fa').removeClass('fa-compress').addClass('fa-expand');
			}
			else {
				btnFS.find('span.fa').addClass('fa-compress').removeClass('fa-expand');
			}
		});
		addCMI(btnFS);
		// sync state
		if (this.container.find('span.change-mode').hasClass('fa-expand')) {
			btnFS.find('span.fa').addClass('fa-expand');
		}
		else {
			btnFS.find('span.fa').addClass('fa-compress');
		}

		// knopje zoomen
		const btnZoom = $('<a id="btnZoom" class="dropdown-item" tabindex="-1"><span class="fa"></span> &nbsp; Zoomen</a>');
		btnZoom.on('click', () => {
			const btn = this.container.find('span.resize.jgallery-btn');
			btn.click();
			// sync state
			if (btn.hasClass('fa-search-minus')) {
				btnZoom.find('span.fa').removeClass('fa-search-minus').addClass('fa-search-plus');
			}
			else {
				btnZoom.find('span.fa').addClass('fa-search-minus').removeClass('fa-search-plus');
			}
		});
		addCMI(btnZoom);
		// sync state
		if (this.container.find('span.resize.jgallery-btn').hasClass('fa-search-plus')) {
			btnZoom.find('span.fa').addClass('fa-search-plus');
		}
		else {
			btnZoom.find('span.fa').addClass('fa-search-minus');
		}

		if (this.magAanpassen) {
			// knopje rechtsom draaien
			const btnRight = $('<a id="btnRight" class="dropdown-item" tabindex="-1"><span class="fa fa-repeat"></span> &nbsp; Draai met de klok mee</a>');
			btnRight.on('click', () => {
				const url = this.getUrl();
				$.post('/fotoalbum/roteren' + dirname(url), {
					foto: basename(url),
					rotation: 90
				}, reload);
			});

			addCMI(btnRight, true);

			// knopje linksom draaien
			const btnLeft = $('<a id="btnLeft" class="dropdown-item" tabindex="-1"><span class="fa fa-undo"></span> &nbsp; Draai tegen de klok in</a>');
			btnLeft.on('click', () => {
				const url = this.getUrl();
				$.post('/fotoalbum/roteren' + dirname(url), {
					foto: basename(url),
					rotation: -90
				}, reload);
			});

			addCMI(btnLeft);

			// knopje albumcover
			const btnCover = $('<a id="btnCover" class="dropdown-item" tabindex="-1"><span class="fa fa-folder"></span> &nbsp; Instellen als albumcover</a>');
			btnCover.on('click', () => {
				const url = this.getUrl();
				$.post('/fotoalbum/albumcover' + dirname(url), {
					foto: basename(url)
				}, redirect);
			});

			addCMI(btnCover);

			// knopje verwijderen
			const btnDel = $('<a id="btnDel" class="dropdown-item" tabindex="-1"><span class="fa fa-times"></span> &nbsp; Verwijderen</a>');
			btnDel.on('click', () => {
				if (!confirm('Foto definitief verwijderen. Weet u het zeker?')) {
					return false;
				}
				const url = this.getUrl();
				$.post('/fotoalbum/verwijderen' + dirname(url), {
					foto: decodeURI(basename(url))
				}, reload);
			});

			addCMI(btnDel, true);
		}
	}
}

$('.fotoalbum').each((i, el) => {
	let wrapper = $(el);
	let fotoalbum = new FotoAlbum(wrapper);
	if (wrapper.data('isLoggedIn')) {
		import(/* webpackChunkName: "fotoalbumtags" */ './FotoAlbumTags').then((FotoAlbumTags) => new FotoAlbumTags(fotoalbum));
	}
});
