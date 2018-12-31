import $ from 'jquery';
import EventEmitter from 'events';
import {basename, dirname, redirect, reload, selectText} from './util';

class FotoAlbumTags {
	tagMode = false;
	tagFormDiv = null;

	constructor(fotoalbum) {
		this.fotoalbum = fotoalbum;

		fotoalbum.on('afterLoadPhoto', () => {
			if (this.tagMode) {
				this.duringTagMode();
			}
			this.loadTags();
		});

		// keyboard shortcuts
		window.addEventListener('keydown', (event) => {
			if (event.keyCode === 46) { // delete
				$('div.fototag.active').each((i, val) => {
					this.removeTag($(val));
				});
			}
			else if (event.keyCode === 27) { // esc
				event.preventDefault();
				if (this.tagMode) {
					if (this.tagFormDiv) {
						this.exitTagForm();
					}
					else {
						this.moveTagDivs();
					}
				}
			}
		});

		window.addEventListener('resize', () => {
			this.moveTagDivs();
			if (this.tagMode) {
				this.duringTagMode();
				if (this.tagFormDiv) {
					this.moveTagForm();
				}
			}
			else {
				if (this.tagFormDiv) {
					this.exitTagForm();
				}
			}
		});

		const btnDelTag = $('<a id="btnDelTag" tabindex="-1"><span class="fa fa-user-times"></span> &nbsp; Etiket verwijderen</a>');
		$('<li></li>').append(btnDelTag).appendTo('#tagMenu');

		// knopje taggen
		const btnTag = $('<span class="fa fa-smile-o jgallery-btn jgallery-btn-small" tooltip="Leden etiketteren"></span>');
		btnTag.on('click', () => {
			if (this.tagMode) {
				this.tagMode = false;
				btnTag.removeClass('fa-toggle-on').addClass('fa-toggle-off');
				FotoAlbumTags.hideTags();
				if (this.tagFormDiv) {
					this.exitTagForm();
				}
				const imgs = this.fotoalbum.container.find('img');
				imgs.css('cursor', '');
				imgs.off('click.newtag');
				// enable nav area on img
				this.fotoalbum.container.find('div.right').show();
				this.fotoalbum.container.find('div.left').show();
			} else {
				this.tagMode = true;
				btnTag.addClass('fa-toggle-on').removeClass('fa-toggle-off');
				this.duringTagMode();
			}
		});
		btnTag.on('mouseenter', () => {
			if (this.tagMode) {
				btnTag.addClass('fa-toggle-on').removeClass('fa-toggle-off');
			}
			else {
				btnTag.removeClass('fa-toggle-on').addClass('fa-toggle-off');
			}
		});
		btnTag.on('mouseout', () => btnTag.removeClass('fa-toggle-on').removeClass('fa-toggle-off').addClass('fa-smile-o'));
		btnTag.appendTo(this.fotoalbum.container.find('div.icons'));

		// toggle thumbnails
		this.fotoalbum.container.find('.minimalize-thumbnails.jgallery-btn').on('click', () => this.moveTagDivs());
	}

	getScreenPos(relX, relY, size) {
		const img = this.fotoalbum.container.find('img.active');
		const parent = img.parent();
		const w = img.width();
		const h = img.height();
		const fotoTopLeft = {
			x: (parent.width() - w) / 2,
			y: (parent.height() - h) / 2
		};
		return {
			x: relX * w / 100 + fotoTopLeft.x,
			y: relY * h / 100 + fotoTopLeft.y,
			size: (w + h) / 200 * size
		};
	}

	drawTag(tag) {
		const pos = this.getScreenPos(tag.x, tag.y, tag.size);
		const tagDiv = $(`<div id="tag${tag.keyword}" class="fototag" title="${tag.name}"></div>`).appendTo(this.fotoalbum.container);
		tagDiv.css({
			top: pos.y - pos.size / 2,
			left: pos.x - pos.size / 2,
			width: pos.size,
			height: pos.size
		});
		if (this.tagMode) {
			tagDiv.addClass('showborder');
		}
		// set attr for move/resize
		tagDiv.data('tag', tag);
		// remove tag handler
		tagDiv.on('click.tag', function () {
			// single selection
			$('div.fototag.active').removeClass('active');
			$(this).addClass('active');
		});
		// tag context menu
		tagDiv.contextMenu({
			menuSelector: '#tagMenu',
			menuSelected: () => {
				this.removeTag(tagDiv);
			}
		});
		return tagDiv;
	}

	drawTags(tags) {
		// remove old ones
		$('div.fototag').remove();
		if (!Array.isArray(tags)) {
			return;
		}
		$.each(tags, (i, tag) => {
			this.drawTag(tag);
		});
		// verberg tags tijdens zoomen
		const zoom = this.fotoalbum.container.find('div.zoom-container');
		if (zoom.attr('data-size') !== 'fit') {
			$('div.fototag').addClass('verborgen');
		}
		else {
			$('div.fototag').removeClass('verborgen');
		}
	}

	loadTags() {
		// remove old ones
		$('div.fototag').remove();
		// get new ones
		const url = this.fotoalbum.getUrl();
		$.post('/fotoalbum/gettags' + dirname(url), {
			foto: basename(url)
		}, (tags) => this.drawTags(tags));
	}

	removeTag(tagDiv) {
		if (confirm('Etiket verwijderen?')) {
			const tag = tagDiv.data('tag');
			$.post('/fotoalbum/removetag', {
				refuuid: tag.refuuid,
				keyword: tag.keyword
			}, (tags) => this.drawTags(tags));
		}
	}

	static showTags() {
		$('div.fototag').addClass('showborder');
	}

	static hideTags() {
		$('div.fototag').removeClass('showborder');
	}

	moveTagDivs() {
		$('div.fototag').each((i, el) => {
			const tag = $(el).data('tag');
			const pos = this.getScreenPos(tag.x, tag.y, tag.size);
			$(this).css({
				top: pos.y - pos.size / 2,
				left: pos.x - pos.size / 2,
				width: pos.size,
				height: pos.size
			});
		});
	}

	drawTagForm(html, relX, relY, size) {
		const pos = this.getScreenPos(relX, relY, size);
		this.tagFormDiv = $(html).appendTo(this.fotoalbum.container);
		this.tagFormDiv.css({
			position: 'absolute',
			top: pos.y + pos.size,
			left: pos.x - pos.size / 2,
			'z-index': 10000
		});
		// set attr for move/resize
		this.tagFormDiv.attr('data-relY', relY);
		this.tagFormDiv.attr('data-relX', relX);
		this.tagFormDiv.attr('data-size', size);
		// set submit handler
		this.tagFormDiv.find('form').data('submitCallback', (response) => {
			if (this.tagFormDiv) {
				this.exitTagForm();
			}
			if (typeof response === 'object') { // JSON tags
				this.drawTags(response);
			}
			else { // HTML form
				this.drawTagForm(response, relX, relY, size);
			}
		});
		// set focus
		this.tagFormDiv.find('input').trigger('focus');
	}

	moveTagForm() {
		if (!this.tagFormDiv) {
			return;
		}
		const pos = this.getScreenPos(this.tagFormDiv.attr('data-relX'), this.tagFormDiv.attr('data-relY'), this.tagFormDiv.attr('data-size'));
		this.tagFormDiv.css({
			top: pos.y + pos.size,
			left: pos.x - (pos.size / 2)
		});
	}

	exitTagForm() {
		$('div[id="tagNew"]').remove();
		this.tagFormDiv.remove();
		this.tagFormDiv = false;
	}

	addTag(relX, relY, size) {
		const url = this.fotoalbum.getUrl();
		$.post('/fotoalbum/addtag' + dirname(url), {
			foto: basename(url),
			x: Math.round(relX),
			y: Math.round(relY),
			size: Math.round(size)
		}, (response) => {
			if (typeof response === 'object') { // JSON tags
				this.drawTags(response);
			}
			else { // HTML form
				this.drawTagForm(response, relX, relY, size);
			}
		});
	}

	newTagStart(e) {
		const img = this.fotoalbum.container.find('img.active');
		// calculate relative position to image top left
		const offset = $(e.target).offset();
		const newTag = {
			x: (e.pageX - offset.left) * 100 / img.width(), // %,
			y: (e.pageY - offset.top) * 100 / img.height(), // %,
			size: 7, // %
			name: '',
			keyword: 'New'
		};
		// show form
		if (this.tagFormDiv) {
			this.exitTagForm();
		}
		this.addTag(newTag.x, newTag.y, newTag.size);
		// show new resizable tag
		const tagDiv = this.drawTag(newTag);
		// not remove-able
		tagDiv.unbind('click.tag');
		// resize-able
		tagDiv.css('cursor', 'nw-resize');
		$(window).off('mouseup.newtag');
		$(window).on('mouseup.newtag', () => {
			$(window).off('mousemove.newtag');
		});
		tagDiv.bind('mousedown.newtag', (e1) => {
			const img = this.fotoalbum.container.find('img.active');
			let prevX = e1.pageX;
			let prevY = e1.pageY;
			$(window).on('mousemove.newtag', (e2) => {
				newTag.size += (e2.pageX - prevX) * 100 / img.width();
				newTag.size += (e2.pageY - prevY) * 100 / img.height();
				prevX = e2.pageX;
				prevY = e2.pageY;
				if (newTag.size < 1) {
					newTag.size = 1;
				}
				else if (newTag.size > 99) {
					newTag.size = 99;
				}
				this.tagFormDiv.find('input[name="size"]').val(Math.round(newTag.size));
				const pos = this.getScreenPos(newTag.x, newTag.y, newTag.size);
				tagDiv.css({
					top: pos.y - pos.size / 2,
					left: pos.x - pos.size / 2,
					width: pos.size,
					height: pos.size
				});
				// update attr for move/resize
				tagDiv.attr('data-size', newTag.size);
			});
		});
	}

	duringTagMode() {
		const zoom = this.fotoalbum.container.find('div.zoom-container');
		if (zoom.attr('data-size') !== 'fit') { // if zoomed in
			alert('Je kunt niet inzoomen tijdens het etiketteren, dat werkt nog niet.');
		}
		if (this.tagFormDiv) {
			this.exitTagForm();
		}
		FotoAlbumTags.showTags();
		// disable nav area on img
		this.fotoalbum.container.find('div.right').hide();
		this.fotoalbum.container.find('div.left').hide();
		// click handler
		const img = this.fotoalbum.container.find('img.active');
		img.css('cursor', 'crosshair');
		// (re-)bind add new tag handler
		img.unbind('click.newtag');
		img.bind('click.newtag', (e) => this.newTagStart(e));
	}
}

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

const initializeerFotoalbum = (wrapper) => {
};

$('.fotoalbum').each((i, el) => {
	let fotoalbum = new FotoAlbum($(el));
	if (wrapper.data('isLoggedIn')) {
		new FotoAlbumTags(fotoalbum);
	}
});
