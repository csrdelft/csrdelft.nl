import {EventEmitter} from 'events';

import 'jgallery/dist/js/jgallery';
import $ from 'jquery';
import {basename, dirname, redirect, reload, selectText} from '../util';

declare global {
	interface JQuery {
		jGallery(options: any): void;

		contextMenu(options: any): void;
	}
}

export class FotoAlbum extends EventEmitter {

	public static requestFullscreen() {
		const docelem = document.querySelector('.jgallery');
		if (docelem) {
			docelem.requestFullscreen();
		}
	}

	public static exitFullScreen() {
		document.exitFullscreen();
	}

	public container: JQuery;
	public wrapper: JQuery;
	public isLoggedIn: boolean;
	public magAanpassen: boolean;
	public slideshowInterval: number;
	public root: string;
	public itemsJson: any;

	constructor(wrapper: JQuery) {
		super();
		this.wrapper = wrapper;
		this.isLoggedIn = wrapper.data('isLoggedIn');
		this.magAanpassen = wrapper.data('magAanpassen');
		this.slideshowInterval = wrapper.data('slideshowInterval');
		this.root = wrapper.data('root');
		this.itemsJson = wrapper.data('fotos');

		const afterLoadPhoto = () => {
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
		};

		wrapper.find('.gallery').jGallery({
			'afterLoadPhoto': afterLoadPhoto,
			'autostart': true,
			'autostartAtAlbum': 1,
			'backgroundColor': 'fff',
			'browserHistory': true,
			'canChangeMode': true,
			'canClose': false,
			'canMinimalizeThumbnails': true,
			'canZoom': true,
			'disabledOnIE8AndOlder': true,
			'draggableZoom': true,
			'draggableZoomHideNavigationOnMobile': true,
			'height': '897px',
			'hideThumbnailsOnInit': false,
			'items': this.itemsJson,
			'maxMobileWidth': 767,
			'mode': 'standard',
			'preloadAll': false,
			'slideshow': true,
			'slideshowAutostart': false,
			'slideshowCanRandom': true,
			'slideshowInterval': this.slideshowInterval,
			'slideshowRandom': false,
			'swipeEvents': false,
			'textColor': '193b61',
			'thumbHeight': 150,
			'thumbHeightOnFullScreen': 150,
			'thumbType': 'image',
			'thumbWidth': 150,
			'thumbWidthOnFullScreen': 150,
			'thumbnails': true,
			'thumbnailsHideOnMobile': false,
			'thumbnailsPosition': 'bottom',
			'title': this.isLoggedIn,
			'titleExpanded': false,
			'tooltipClose': 'Close',
			'tooltipFullScreen': 'Full screen',
			'tooltipRandom': 'Random',
			'tooltipSeeAllPhotos': 'Grid',
			'tooltipSeeOtherAlbums': 'Toon sub-albums',
			'tooltipSlideshow': 'Slideshow',
			'tooltipToggleThumbnails': 'Toggle thumbnails',
			'tooltipZoom': 'Zoom',
			'tooltips': true,
			'transition': 'moveToLeft_scaleUp',
			'transitionBackward': 'moveToRight_scaleUp',
			'transitionCols': '1',
			'transitionDuration': '0.7s',
			'transitionRows': '1',
			'transitionTimingFunction': 'cubic-bezier(0,1,1,1)',
			'width': '100%',
			'zoomSize': 'fit',
			'zoomSize:': 'original',
		});
		this.container = $('div.jgallery');
		this.container.addClass('noselect');

		$(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange', () => {
			if (document.fullscreenEnabled) {
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
			$('<span class="fa fa-level-up jgallery-btn jgallery-btn-small" tooltip="Open parent album"></span>')
				.on('click', () => {
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
			} else {
				$('div.fototag').removeClass('verborgen');
			}
		}).appendTo(this.container.find('div.icons'));

		// toggle full screen
		this.container.find('span.change-mode')
			.on('click', () => this.toggleFullScreen()).appendTo(this.container.find('div.icons'));
		// fullscreen GET param
		if (window.location.href.indexOf('?fullscreen') > 0 && !this.container.hasClass('jgallery-full-screen')) {
			$('span.change-mode').trigger('click');
		}

		if (this.isLoggedIn) {
			// foto context menu
			this.container.find('div.zoom').contextMenu({
				menuSelected: () => true,
				menuSelector: '#contextMenu',
			});
		}
		// mode change album selector to last position
		this.container.find('div.icons .jgallery-btn.change-album').appendTo(this.container.find('div.icons'));
	}

	public getFullUrl() {
		return decodeURI(this.container.find('div.nav-bottom div.title').html());
	}

	public getUrl() {
		return this.getFullUrl().replace(this.root, '');
	}

	public toggleFullScreen() {
		if (this.container.hasClass('jgallery-full-screen')) {
			FotoAlbum.requestFullscreen();
		} else {
			FotoAlbum.exitFullScreen();
		}
	}

	public showFullRes() {
		const zoom = this.container.find('div.zoom-container');
		const foto = zoom.find('img.active');
		const fotoElem = foto.get(0) as HTMLImageElement;
		const setDimensions = (img: HTMLImageElement) => {
			if (zoom.attr('data-size') === 'original') {
				foto.css({
					'height': img.naturalHeight,
					'margin-left': -img.naturalWidth / 2,
					'margin-top': -img.naturalHeight / 2,
					'max-height': '',
					'max-width': '',
					'width': img.naturalWidth,
				});
				foto.attr('data-width', img.naturalWidth);
				foto.attr('data-height', img.naturalHeight);
				$(window).trigger('resize');
			}
		};
		if (fotoElem.src !== this.getFullUrl()) {
			fotoElem.src = this.getFullUrl();
		}
		if (zoom.attr('data-size') === 'original') {
			setDimensions(fotoElem);
		}
		if (zoom.attr('data-size') === 'fill') {
			$('span.resize.jgallery-btn').removeClass('fa-search-minus').addClass('fa-search-plus');
		}
	}

	public createCMBtn(id: string, text: string, icon: string) {
		return $('<a>')
			.attr({
				class: 'dropdown-item',
				id,
				tabindex: '-1',
			})
			.append($('<span>').attr('class', `fa fa-${icon}`))
			.append(`&nbsp; ${text}`);
	}

	public updateContextMenu() {
		const cm = $('#contextMenu').empty();
		const addCMI = (item: JQuery, divider?: boolean) => {
			if (divider) {
				$('<div class="dropdown-divider"></div>').appendTo(cm);
			}
			$(item).appendTo(cm);
			if (item.hasClass('disabled')) {
				item.parent().addClass('disabled');
			}
		};

		// knopje downloaden
		const btnDown = this.createCMBtn('btnDown', 'Downloaden', 'download');
		btnDown.on('click', () => window.location.href = `/fotoalbum/download${this.getUrl()}`);
		addCMI(btnDown);

		// knopje taggen
		const btnTag = this.createCMBtn('btnTag', 'Leden etiketteren', 'smile-o');
		btnTag.on('click', () => this.container.find('span.fa-smile-o.jgallery-btn').click());
		addCMI(btnTag);

		// knopje full screen
		const btnFS = this.createCMBtn('btnFS', 'Volledig scherm', '');
		btnFS.on('click', () => {
			const btn = this.container.find('span.change-mode');
			btn.click();
			// sync state
			if (btn.hasClass('fa-expand')) {
				btnFS.find('span.fa').removeClass('fa-compress').addClass('fa-expand');
			} else {
				btnFS.find('span.fa').addClass('fa-compress').removeClass('fa-expand');
			}
		});
		addCMI(btnFS);
		// sync state
		if (this.container.find('span.change-mode').hasClass('fa-expand')) {
			btnFS.find('span.fa').addClass('fa-expand');
		} else {
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
			} else {
				btnZoom.find('span.fa').addClass('fa-search-minus').removeClass('fa-search-plus');
			}
		});
		addCMI(btnZoom);
		// sync state
		if (this.container.find('span.resize.jgallery-btn').hasClass('fa-search-plus')) {
			btnZoom.find('span.fa').addClass('fa-search-plus');
		} else {
			btnZoom.find('span.fa').addClass('fa-search-minus');
		}

		if (this.magAanpassen) {
			// knopje rechtsom draaien
			const btnRight = this.createCMBtn('btnRight', 'Draai met de klok mee', 'repeat');
			btnRight.on('click', () => {
				const url = this.getUrl();
				$.post('/fotoalbum/roteren' + dirname(url), {
					foto: basename(url),
					rotation: 90,
				}, reload);
			});

			addCMI(btnRight, true);

			// knopje linksom draaien
			const btnLeft = this.createCMBtn('btnLeft', 'Draai tegen de klok in', 'undo');
			btnLeft.on('click', () => {
				const url = this.getUrl();
				$.post('/fotoalbum/roteren' + dirname(url), {
					foto: basename(url),
					rotation: -90,
				}, reload);
			});

			addCMI(btnLeft);

			// knopje albumcover
			const btnCover = this.createCMBtn('btnCover', 'Instellen als albumcover', 'folder');
			btnCover.on('click', () => {
				const url = this.getUrl();
				$.post('/fotoalbum/albumcover' + dirname(url), {
					foto: basename(url),
				}, redirect);
			});

			addCMI(btnCover);

			// knopje verwijderen
			const btnDel = this.createCMBtn('btnDel', 'Verwijderen', 'times');
			btnDel.on('click', () => {
				if (!confirm('Foto definitief verwijderen. Weet u het zeker?')) {
					return false;
				}
				const url = this.getUrl();
				$.post('/fotoalbum/verwijderen' + dirname(url), {
					foto: decodeURI(basename(url)),
				}, reload);
			});

			addCMI(btnDel, true);
		}
	}
}

$('.fotoalbum').each((i, el) => {
	const wrapper = $(el);
	const fotoalbum = new FotoAlbum(wrapper);
	if (wrapper.data('isLoggedIn')) {
		// Laad de FotoAlbumTags code niet als dat niet nodig is.
		import(/* webpackChunkName: "fotoalbumtags" */ './FotoAlbumTags')
			.then(({FotoAlbumTags}) => new FotoAlbumTags(fotoalbum));
	}
});
