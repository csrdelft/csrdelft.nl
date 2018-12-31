import $ from 'jquery';
import {basename, dirname} from '../util';

export default class FotoAlbumTags {
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

	static showTags() {
		$('div.fototag').addClass('showborder');
	}

	static hideTags() {
		$('div.fototag').removeClass('showborder');
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
		tagDiv.off('click.tag');
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
