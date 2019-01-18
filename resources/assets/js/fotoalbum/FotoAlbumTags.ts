import axios from 'axios';
import $ from 'jquery';
import {basename, dirname} from '../util';
import {FotoAlbum} from './FotoAlbum';

interface Position {
	x: number;
	y: number;
	size: number;
}

interface Tag extends Position {
	name: string;
	keyword: string;
}

export class FotoAlbumTags {

	public static showTags() {
		$('div.fototag').addClass('showborder');
	}

	public static hideTags() {
		$('div.fototag').removeClass('showborder');
	}
	public tagMode = false;
	public tagFormDiv: JQuery | null;

	public fotoalbum: FotoAlbum;

	constructor(fotoalbum: FotoAlbum) {
		this.fotoalbum = fotoalbum;

		fotoalbum.on('afterLoadPhoto', () => {
			if (this.tagMode) {
				this.duringTagMode();
			}
			this.loadTags();
		});

		// keyboard shortcuts
		window.addEventListener('keydown', (event) => {
			if (event.key === 'Delete') { // delete
				$('div.fototag.active').each((i, val) => {
					this.removeTag($(val));
				});
			} else if (event.key === 'Escape') { // esc
				event.preventDefault();
				if (this.tagMode) {
					if (this.tagFormDiv) {
						this.exitTagForm();
					} else {
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
			} else {
				if (this.tagFormDiv) {
					this.exitTagForm();
				}
			}
		});

		const btnDelTag = this.fotoalbum.createCMBtn('btnDelTag', 'Etiket verwijderen', 'user-times');
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
			} else {
				btnTag.removeClass('fa-toggle-on').addClass('fa-toggle-off');
			}
		});
		btnTag.on('mouseout', () => btnTag.removeClass('fa-toggle-on').removeClass('fa-toggle-off').addClass('fa-smile-o'));
		btnTag.insertBefore(this.fotoalbum.container.find('div.icons .change-album'));

		// toggle thumbnails
		this.fotoalbum.container.find('.minimalize-thumbnails.jgallery-btn').on('click', () => this.moveTagDivs());
	}

	public getScreenPos(position: Position): Position {
		const img = this.fotoalbum.container.find('img.active');
		const parent = img.parent();
		const w = img.width()!;
		const h = img.height()!;
		const fotoTopLeft = {
			x: (parent.width()! - w) / 2,
			y: (parent.height()! - h) / 2,
		};
		return {
			size: (w + h) / 200 * position.size,
			x: position.x * w / 100 + fotoTopLeft.x,
			y: position.y * h / 100 + fotoTopLeft.y,
		};
	}

	public drawTag(tag: Tag) {
		const screenPosition = this.getScreenPos(tag);
		const tagDiv = $(`<div id="tag${tag.keyword}" class="fototag" title="${tag.name}"></div>`)
			.appendTo(this.fotoalbum.container);
		tagDiv.css({
			height: screenPosition.size,
			left: screenPosition.x - screenPosition.size / 2,
			top: screenPosition.y - screenPosition.size / 2,
			width: screenPosition.size,
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
			menuSelected: () => {
				this.removeTag(tagDiv);
			},
			menuSelector: '#tagMenu',
		});
		return tagDiv;
	}

	public drawTags(tags: Tag[]) {
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
		} else {
			$('div.fototag').removeClass('verborgen');
		}
	}

	public loadTags() {
		// remove old ones
		$('div.fototag').remove();
		// get new ones
		const url = this.fotoalbum.getUrl();
		const data = new FormData();
		data.append('foto', basename(url));
		axios
			.post('/fotoalbum/gettags' + dirname(url), data).then((response) => this.drawTags(response.data));
	}

	public removeTag(tagDiv: JQuery) {
		if (confirm('Etiket verwijderen?')) {
			const tag = tagDiv.data('tag');
			const data = new FormData();
			data.append('refuuid', tag.refuuid);
			data.append('keyword', tag.keyword);
			axios
				.post('/fotoalbum/removetag', data)
				.then((response) => this.drawTags(response.data));
		}
	}

	public moveTagDivs() {
		$('div.fototag').each((i, el) => {
			const tag = $(el).data('tag') as Tag;
			const pos = this.getScreenPos(tag);
			$(this).css({
				height: pos.size,
				left: pos.x - pos.size / 2,
				top: pos.y - pos.size / 2,
				width: pos.size,
			});
		});
	}

	public drawTagForm(html: string, position: Position) {
		const pos = this.getScreenPos(position);
		this.tagFormDiv = $(html).appendTo(this.fotoalbum.container);
		this.tagFormDiv.css({
			'left': pos.x - pos.size / 2,
			'position': 'absolute',
			'top': pos.y + pos.size,
			'z-index': 10000,
		});
		// set attr for move/resize
		this.tagFormDiv.data('tagPosition', position);
		// set submit handler
		this.tagFormDiv.find('form').data('submitCallback', (response: Tag[] | string) => {
			if (this.tagFormDiv) {
				this.exitTagForm();
			}
			if (typeof response === 'object') { // JSON tags
				this.drawTags(response);
			} else { // HTML form
				this.drawTagForm(response, position);
			}
		});
		// set focus
		setTimeout(() => this.tagFormDiv && this.tagFormDiv.find('input[name="uid"]').trigger('focus'));
	}

	public moveTagForm() {
		if (!this.tagFormDiv) {
			return;
		}
		const pos = this.getScreenPos(this.tagFormDiv.data('tagPosition') as Position);
		this.tagFormDiv.css({
			left: pos.x - (pos.size / 2),
			top: pos.y + pos.size,
		});
	}

	public exitTagForm() {
		$('div[id="tagNew"]').remove();
		if (this.tagFormDiv) { this.tagFormDiv.remove(); }
		this.tagFormDiv = null;
	}

	public addTag(position: Position) {
		const url = this.fotoalbum.getUrl();
		const data = new FormData();
		data.append('foto', basename(url));
		data.append('x', Math.round(position.x).toString());
		data.append('y', Math.round(position.y).toString());
		data.append('size', Math.round(position.size).toString());
		axios.post('/fotoalbum/addtag' + dirname(url), data)
			.then((response) => {
				if (typeof response.data === 'object') { // JSON tags
					this.drawTags(response.data);
				} else { // HTML form
					this.drawTagForm(response.data, position);
				}
			});
	}

	public newTagStart(e: JQuery.ClickEvent) {
		const img = this.fotoalbum.container.find('img.active');
		const target = e.target as HTMLElement;
		// calculate relative position to image top left
		const offset = $(target).offset()!;
		const width = img.width()!;
		const height = img.height()!;
		const newTag = {
			keyword: 'New',
			name: '',
			size: 7, // %
			x: (e.pageX - offset.left) * 100 / width, // %,
			y: (e.pageY - offset.top) * 100 / height, // %,
		} as Tag;
		// show form
		if (this.tagFormDiv) {
			this.exitTagForm();
		}
		this.addTag(newTag);
		// show new resizable tag
		const tagDiv = this.drawTag(newTag);
		// not remove-able
		tagDiv.off('click.tag');
		// resize-able
		tagDiv.css('cursor', 'nw-resize');
		$(window).off('mouseup.newtag');
		$(window).on('mouseup.newtag', () => $(window).off('mousemove.newtag'));
		tagDiv.on('mousedown.newtag', (e1: JQuery.MouseDownEvent) => {
			const imgActive = this.fotoalbum.container.find('img.active');
			let prevX = e1.pageX;
			let prevY = e1.pageY;
			$(window).on('mousemove.newtag', (e2: JQuery.MouseMoveEvent) => {
				newTag.size += (e2.pageX - prevX) * 100 / imgActive.width()!;
				newTag.size += (e2.pageY - prevY) * 100 / imgActive.height()!;
				prevX = e2.pageX;
				prevY = e2.pageY;
				if (newTag.size < 1) {
					newTag.size = 1;
				} else if (newTag.size > 99) {
					newTag.size = 99;
				}
				if (this.tagFormDiv) { this.tagFormDiv.find('input[name="size"]').val(Math.round(newTag.size)); }
				const pos = this.getScreenPos(newTag);
				tagDiv.css({
					height: pos.size,
					left: pos.x - pos.size / 2,
					top: pos.y - pos.size / 2,
					width: pos.size,
				});
				// update attr for move/resize
				tagDiv.attr('data-size', newTag.size);
			});
		});
	}

	public duringTagMode() {
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
		img.off('click.newtag');
		img.on('click', (e) => this.newTagStart(e));
	}
}
