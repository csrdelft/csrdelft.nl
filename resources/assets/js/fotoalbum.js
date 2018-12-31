import $ from 'jquery';
import {basename, dirname, redirect, reload, selectText} from './util';

const initializeerFotoalbum = (wrapper) => {
	let tagMode = false;
	let container;

	let isLoggedIn = wrapper.data('isLoggedIn'),
		magAanpassen = wrapper.data('magAanpassen'),
		slideshowInterval = wrapper.data('slideshowInterval'),
		root = wrapper.data('root'),
		itemsJson = wrapper.data('fotos');

	// BEGIN tagging code
	const getScreenPos = function (relX, relY, size) {
		const img = container.find('img.active');
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
	};
	const drawTag = function (tag) {
		const pos = getScreenPos(tag.x, tag.y, tag.size);
		const tagDiv = $(`<div id="tag${tag.keyword}" class="fototag" title="${tag.name}"></div>`).appendTo(container);
		tagDiv.css({
			top: pos.y - pos.size / 2,
			left: pos.x - pos.size / 2,
			width: pos.size,
			height: pos.size
		});
		if (tagMode) {
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
				removeTag(tagDiv);
			}
		});
		return tagDiv;
	};
	const drawTags = function (tags) {
		// remove old ones
		$('div.fototag').remove();
		if (!Array.isArray(tags)) {
			return;
		}
		$.each(tags, function (i, tag) {
			drawTag(tag);
		});
		// verberg tags tijdens zoomen
		const zoom = container.find('div.zoom-container');
		if (zoom.attr('data-size') !== 'fit') {
			$('div.fototag').addClass('verborgen');
		}
		else {
			$('div.fototag').removeClass('verborgen');
		}
	};
	const getFullUrl = function () {
		return decodeURI(container.find('div.nav-bottom div.title').html());
	};
	const getUrl = function () {
		return getFullUrl().replace(root, '');
	};
	const loadTags = function () {
		// remove old ones
		$('div.fototag').remove();
		// get new ones
		const url = getUrl();
		$.post('/fotoalbum/gettags' + dirname(url), {
			foto: basename(url)
		}, drawTags);
	};
	const removeTag = function (tagDiv) {
		if (confirm('Etiket verwijderen?')) {
			const tag = tagDiv.data('tag');
			$.post('/fotoalbum/removetag', {
				refuuid: tag.refuuid,
				keyword: tag.keyword
			}, drawTags);
		}
	};
	const showTags = function () {
		$('div.fototag').addClass('showborder');
	};
	const hideTags = function () {
		$('div.fototag').removeClass('showborder');
	};
	const moveTagDivs = function () {
		$('div.fototag').each(function () {
			const tag = $(this).data('tag');
			const pos = getScreenPos(tag.x, tag.y, tag.size);
			$(this).css({
				top: pos.y - pos.size / 2,
				left: pos.x - pos.size / 2,
				width: pos.size,
				height: pos.size
			});
		});
	};
	let tagFormDiv = false;
	const drawTagForm = function (html, relX, relY, size) {
		const pos = getScreenPos(relX, relY, size);
		tagFormDiv = $(html).appendTo(container);
		tagFormDiv.css({
			position: 'absolute',
			top: pos.y + pos.size,
			left: pos.x - pos.size / 2,
			'z-index': 10000
		});
		// set attr for move/resize
		tagFormDiv.attr('data-relY', relY);
		tagFormDiv.attr('data-relX', relX);
		tagFormDiv.attr('data-size', size);
		// set submit handler
		tagFormDiv.find('form').data('submitCallback', function (response) {
			if (tagFormDiv) {
				exitTagForm();
			}
			if (typeof response === 'object') { // JSON tags
				drawTags(response);
			}
			else { // HTML form
				drawTagForm(response, relX, relY, size);
			}
		});
		// set focus
		tagFormDiv.find('input').trigger('focus');
	};
	const moveTagForm = function () {
		if (!tagFormDiv) {
			return;
		}
		const pos = getScreenPos(tagFormDiv.attr('data-relX'), tagFormDiv.attr('data-relY'), tagFormDiv.attr('data-size'));
		tagFormDiv.css({
			top: pos.y + pos.size,
			left: pos.x - (pos.size / 2)
		});
	};
	const exitTagForm = function () {
		$('div[id="tagNew"]').remove();
		tagFormDiv.remove();
		tagFormDiv = false;
	};
	const addTag = function (relX, relY, size) {
		const url = getUrl();
		$.post('/fotoalbum/addtag' + dirname(url), {
			foto: basename(url),
			x: Math.round(relX),
			y: Math.round(relY),
			size: Math.round(size)
		}, function (response) {
			if (typeof response === 'object') { // JSON tags
				drawTags(response);
			}
			else { // HTML form
				drawTagForm(response, relX, relY, size);
			}
		});
	};
	const newTagStart = function (e) {
		const img = container.find('img.active');
		// calculate relative position to image top left
		const offset = $(this).offset();
		const newTag = {
			x: (e.pageX - offset.left) * 100 / img.width(), // %,
			y: (e.pageY - offset.top) * 100 / img.height(), // %,
			size: 7, // %
			name: '',
			keyword: 'New'
		};
		// show form
		if (tagFormDiv) {
			exitTagForm();
		}
		addTag(newTag.x, newTag.y, newTag.size);
		// show new resizable tag
		const tagDiv = drawTag(newTag);
		// not remove-able
		tagDiv.unbind('click.tag');
		// resize-able
		tagDiv.css('cursor', 'nw-resize');
		$(window).off('mouseup.newtag');
		$(window).on('mouseup.newtag', function () {
			$(window).off('mousemove.newtag');
		});
		tagDiv.bind('mousedown.newtag', function (e1) {
			const img = container.find('img.active');
			let prevX = e1.pageX;
			let prevY = e1.pageY;
			$(window).on('mousemove.newtag', function (e2) {
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
				tagFormDiv.find('input[name="size"]').val(Math.round(newTag.size));
				const pos = getScreenPos(newTag.x, newTag.y, newTag.size);
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
	};
	const duringTagMode = function () {
		const zoom = container.find('div.zoom-container');
		if (zoom.attr('data-size') !== 'fit') { // if zoomed in
			alert('Je kunt niet inzoomen tijdens het etiketteren, dat werkt nog niet.');
		}
		if (tagFormDiv) {
			exitTagForm();
		}
		showTags();
		// disable nav area on img
		container.find('div.right').hide();
		container.find('div.left').hide();
		// click handler
		const img = container.find('img.active');
		img.css('cursor', 'crosshair');
		// (re-)bind add new tag handler
		img.unbind('click.newtag');
		img.bind('click.newtag', newTagStart);
	};
	$(window).on('resize', () => {
		moveTagDivs();
		if (tagMode) {
			duringTagMode();
			if (tagFormDiv) {
				moveTagForm();
			}
		}
		else {
			if (tagFormDiv) {
				exitTagForm();
			}
		}
	});
	// tag context menu
	const btnDelTag = $('<a id="btnDelTag" tabindex="-1"><span class="fa fa-user-times"></span> &nbsp; Etiket verwijderen</a>');
	$('<li></li>').append(btnDelTag).appendTo('#tagMenu');
	// END tagging code
	// BEGIN toggle full screen
	const toggleFullScreen = () => {
		//moveTagDivs();
		if (container.hasClass('jgallery-full-screen')) {
			requestFullscreen();
		}
		else {
			exitFullScreen();
		}
	};
	const requestFullscreen = () => {
			const docelem = $('.jgallery').get(0);
			if (docelem.requestFullscreen) {
				docelem.requestFullscreen();
			} else if (docelem.webkitRequestFullscreen) {
				docelem.webkitRequestFullscreen();
			} else if (docelem.mozRequestFullScreen) {
				docelem.mozRequestFullScreen();
			} else if (docelem.msRequestFullscreen) {
				docelem.msRequestFullscreen();
			}
		},
		exitFullScreen = () => {
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
		};
	// END toggle full screen

	// BEGIN zoom full resolution
	const showFullRes = function () {
		const zoom = container.find('div.zoom-container');
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
		if (foto[0].src !== getFullUrl()) {
			foto[0].src = getFullUrl();
		}
		if (zoom.attr('data-size') === 'original') {
			setDimensions(foto[0]);
		}
		if (zoom.attr('data-size') === 'fill') {
			$('span.resize.jgallery-btn').removeClass('fa-search-minus').addClass('fa-search-plus');
		}
	};
	// END zoom full resolution

	// BEGIN dynamic context menu
	const updateContextMenu = function () {
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

		if (isLoggedIn) {
			// knopje downloaden
			const btnDown = $('<a id="btnDown" class="dropdown-item" tabindex="-1"><span class="fa fa-download"></span> &nbsp; Downloaden</a>');
			btnDown.on('click', () => window.location.href = `/fotoalbum/download${getUrl()}`);
			addCMI(btnDown);
		}
		if (isLoggedIn) {
			// knopje taggen
			const btnTag = $('<a id="btnTag" class="dropdown-item" tabindex="-1"><span class="fa fa-smile-o"></span> &nbsp; Leden etiketteren</a>');
			btnTag.on('click', () => container.find('span.fa-smile-o.jgallery-btn').click());
			addCMI(btnTag);
		}

		// knopje full screen
		const btnFS = $('<a id="btnFS" class="dropdown-item" tabindex="-1"><span class="fa"></span> &nbsp; Volledig scherm</a>');
		btnFS.on('click', () => {
			const btn = container.find('span.change-mode');
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
		if (container.find('span.change-mode').hasClass('fa-expand')) {
			btnFS.find('span.fa').addClass('fa-expand');
		}
		else {
			btnFS.find('span.fa').addClass('fa-compress');
		}

		// knopje zoomen
		const btnZoom = $('<a id="btnZoom" class="dropdown-item" tabindex="-1"><span class="fa"></span> &nbsp; Zoomen</a>');
		btnZoom.on('click', () => {
			const btn = container.find('span.resize.jgallery-btn');
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
		if (container.find('span.resize.jgallery-btn').hasClass('fa-search-plus')) {
			btnZoom.find('span.fa').addClass('fa-search-plus');
		}
		else {
			btnZoom.find('span.fa').addClass('fa-search-minus');
		}

		if (magAanpassen) {
			// knopje rechtsom draaien
			const btnRight = $('<a id="btnRight" class="dropdown-item" tabindex="-1"><span class="fa fa-repeat"></span> &nbsp; Draai met de klok mee</a>');
			btnRight.on('click', () => {
				const url = getUrl();
				$.post('/fotoalbum/roteren' + dirname(url), {
					foto: basename(url),
					rotation: 90
				}, reload);
			});

			addCMI(btnRight, true);

			// knopje linksom draaien
			const btnLeft = $('<a id="btnLeft" class="dropdown-item" tabindex="-1"><span class="fa fa-undo"></span> &nbsp; Draai tegen de klok in</a>');
			btnLeft.on('click', function () {
				const url = getUrl();
				$.post('/fotoalbum/roteren' + dirname(url), {
					foto: basename(url),
					rotation: -90
				}, reload);
			});

			addCMI(btnLeft);

			// knopje albumcover
			const btnCover = $('<a id="btnCover" class="dropdown-item" tabindex="-1"><span class="fa fa-folder"></span> &nbsp; Instellen als albumcover</a>');
			btnCover.on('click', function () {
				const url = getUrl();
				$.post('/fotoalbum/albumcover' + dirname(url), {
					foto: basename(url)
				}, redirect);
			});

			addCMI(btnCover);

			// knopje verwijderen
			const btnDel = $('<a id="btnDel" class="dropdown-item" tabindex="-1"><span class="fa fa-times"></span> &nbsp; Verwijderen</a>');
			btnDel.on('click', function () {
				if (!confirm('Foto definitief verwijderen. Weet u het zeker?')) {
					return false;
				}
				const url = getUrl();
				$.post('/fotoalbum/verwijderen' + dirname(url), {
					foto: decodeURI(basename(url))
				}, reload);
			});

			addCMI(btnDel, true);
		}
		// END dynamic context menu
	};
	wrapper.find('.gallery').jGallery({
		'width': '100%',
		'height': '897px',
		'mode': 'standard',
		'canChangeMode': true,
		'swipeEvents': false,
		'browserHistory': true,
		'disabledOnIE8AndOlder': true,
		'preloadAll': false,
		'maxMobileWidth': 767,
		'draggableZoomHideNavigationOnMobile': true,
		'autostart': true,
		'autostartAtAlbum': 1,
		'canZoom': true,
		'draggableZoom': true,
		'zoomSize': 'fit',
		'zoomSize:': 'original',
		'backgroundColor': 'fff',
		'textColor': '193b61',
		'thumbnails': true,
		'thumbType': 'image',
		'thumbWidth': 150,
		'thumbHeight': 150,
		'thumbWidthOnFullScreen': 150,
		'thumbHeightOnFullScreen': 150,
		'thumbnailsPosition': 'bottom',
		'hideThumbnailsOnInit': false,
		'canMinimalizeThumbnails': true,
		'thumbnailsHideOnMobile': false,
		'transition': 'moveToLeft_scaleUp',
		'transitionBackward': 'moveToRight_scaleUp',
		'transitionTimingFunction': 'cubic-bezier(0,1,1,1)',
		'transitionDuration': '0.7s',
		'transitionCols': '1',
		'transitionRows': '1',
		'title': isLoggedIn,
		'titleExpanded': false,
		'tooltips': true,
		'tooltipZoom': 'Zoom',
		'tooltipToggleThumbnails': 'Toggle thumbnails',
		'tooltipSeeAllPhotos': 'Grid',
		'tooltipSeeOtherAlbums': 'Toon sub-albums',
		'tooltipSlideshow': 'Slideshow',
		'slideshowInterval': slideshowInterval,
		'slideshow': true,
		'slideshowAutostart': false,
		'slideshowRandom': false,
		'slideshowCanRandom': true,
		'tooltipRandom': 'Random',
		'tooltipFullScreen': 'Full screen',
		'tooltipClose': 'Close',
		'canClose': false,
		'initGallery': function () {
		},
		'showGallery': function () {
		},
		'closeGallery': function () {
		},
		'beforeLoadPhoto': function () {
		},
		'showPhoto': function () {
		},
		'afterLoadPhoto': function () {
			container = $('div.jgallery');
			if (isLoggedIn) {
				// dynamic context menu
				updateContextMenu();
			}
			const zoom = container.find('div.zoom-container');
			// if zoomed in
			if (zoom.attr('data-size') !== 'fit') {
				showFullRes();
			}
			if (isLoggedIn) {
				if (tagMode) {
					duringTagMode();
				}
				loadTags();
			}
		},
		'items': itemsJson
	});
	container = $('div.jgallery');
	container.addClass('noselect');

	// keyboard shortcuts
	$(window).on('keydown', function (event) {
		if (event.keyCode === 46) { // delete
			$('div.fototag.active').each(function () {
				removeTag($(this));
			});
		}
		else if (event.keyCode === 27) { // esc
			event.preventDefault();
			if (tagMode) {
				if (tagFormDiv) {
					exitTagForm();
				}
				else {
					moveTagDivs();
				}
			}
		}
	});

	$(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange', function () {
		if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
			// Fullscreen gegaan, door ons knopje.
		} else if (container.hasClass('jgallery-full-screen')) {
			$('span.change-mode').trigger('click');
		}
	});

	if (isLoggedIn) {

		// user selectable foto url
		container.find('div.title').off().on('click', function () {
			selectText(this);
		}).addClass('select-text');

		// knopje map omhoog
		$('<span class="fa fa-level-up jgallery-btn jgallery-btn-small" tooltip="Open parent album"></span>').click(function () {
			const url = getUrl();
			let fullscreen = '';
			if (container.hasClass('jgallery-full-screen')) {
				fullscreen = '?fullscreen';
			}
			window.location.href = window.util.dirname(window.util.dirname(url)).replace('plaetjes/', '') + fullscreen;
		}).prependTo(container.find('div.icons'));

		// toggle thumbnails
		container.find('.minimalize-thumbnails.jgallery-btn').on('click', moveTagDivs);
	}

	// knopje subalbums
	container.find('.fa-list-ul').removeClass('fa-list-ul').addClass('fa-folder-open-o');

	// zoom button
	$('span.resize.jgallery-btn').on('click', function () {
		const zoom = container.find('div.zoom-container');
		if (zoom.attr('data-size') !== 'fit') {
			showFullRes();
			// verberg tags tijdens zoomen
			$('div.fototag').addClass('verborgen');
		}
		else {
			$('div.fototag').removeClass('verborgen');
		}
	}).appendTo(container.find('div.icons'));

	// toggle full screen
	container.find('span.change-mode').on('click', toggleFullScreen).appendTo(container.find('div.icons'));
	// fullscreen GET param
	if (window.location.href.indexOf('?fullscreen') > 0 && !container.hasClass('jgallery-full-screen')) {
		$('span.change-mode').trigger('click');
	}

	if (isLoggedIn) {
		// foto context menu
		container.find('div.zoom').contextMenu({
			menuSelector: '#contextMenu',
			menuSelected: function () {
			}
		});

		// knopje taggen
		const btnTag = $('<span class="fa fa-smile-o jgallery-btn jgallery-btn-small" tooltip="Leden etiketteren"></span>');
		btnTag.on('click', function () {
			if (tagMode) {
				tagMode = false;
				$(this).removeClass('fa-toggle-on').addClass('fa-toggle-off');
				hideTags();
				if (tagFormDiv) {
					exitTagForm();
				}
				const imgs = container.find('img');
				imgs.css('cursor', '');
				imgs.off('click.newtag');
				// enable nav area on img
				container.find('div.right').show();
				container.find('div.left').show();
			}
			else {
				tagMode = true;
				$(this).addClass('fa-toggle-on').removeClass('fa-toggle-off');
				duringTagMode();
			}
		});
		btnTag.on('mouseenter', function () {
			if (tagMode) {
				$(this).addClass('fa-toggle-on').removeClass('fa-toggle-off');
			}
			else {
				$(this).removeClass('fa-toggle-on').addClass('fa-toggle-off');
			}
		});
		btnTag.on('mouseout', function () {
			$(this).removeClass('fa-toggle-on').removeClass('fa-toggle-off').addClass('fa-smile-o');
		});
		btnTag.appendTo(container.find('div.icons'));
	}
	// mode change album selector to last position
	container.find('div.icons .jgallery-btn.change-album').appendTo(container.find('div.icons'));
};

$('.fotoalbum').each(function () {
	initializeerFotoalbum($(this));
});
