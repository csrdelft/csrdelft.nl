import axios from 'axios';
import {Size} from 'jgallery/src/preview';
import createElement from 'jgallery/src/utils/create-element';
import withTooltip from 'jgallery/src/utils/with-tooltip';
import AlbumItem from 'jgallery/types/album-item';
import {GalleryDecorator} from 'jgallery/types/gallery';
import Params from 'jgallery/types/gallery/parameters';
import $ from 'jquery';
import {basename, dirname} from '../util';
import {select} from "../dom";

interface Position {
	x: number;
	y: number;
	size: number;
}

interface Tag extends Position {
	name: string;
	keyword: string;
}

const withTags: GalleryDecorator = (constructor) =>
	class extends constructor {

		private get imageElement() {
			try {
				return select<HTMLImageElement>('.j-gallery-preview-content', this.previewElement)
			} catch (e) {
				return null
			}
		}

		private get left() {
			return select<HTMLElement>('.j-gallery-left', this.previewElement)
		}

		private get right() {
			return select<HTMLElement>('.j-gallery-right', this.previewElement)
		}

		private tagMode = false;
		private tagFormDiv: HTMLElement | null;
		private readonly root: string;
		private readonly tagContainer: HTMLElement;
		private activeTag: HTMLElement | null;

		private currentItem: AlbumItem;

		constructor(albums: AlbumItem[], params: Params) {
			super(albums, params);
			this.root = params.root;

			this.tagContainer = createElement(`<div class="tag-container"></div>`);
			this.previewElement.appendChild(this.tagContainer);
			const tagIcon = createElement('<i class="fa fa-smile"></i>');

			const tagButton = createElement(`<span class="j-gallery-icon"></span>`, {
				children: [tagIcon],
			});
			withTooltip(tagButton, {
				style: {
					color: params.backgroundColor,
					background: params.textColor,
					transform: 'translateY(-8px)',
				},
				content: 'Leden etiketteren',
			});
			this.appendControlsElements([tagButton]);

			window.addEventListener('keydown', (event) => {
				if (event.key === 'Delete') { // delete
					if (this.activeTag) {
						this.removeTag(this.activeTag);
					}
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

			// knopje taggen
			tagButton.addEventListener('click', () => {
				if (this.tagMode) {
					this.tagMode = false;
					tagIcon.classList.replace('fa-toggle-on', 'fa-toggle-off')
					this.hideTags();
					if (this.tagFormDiv) {
						this.exitTagForm();
					}
					this.imageElement.style.cursor = '';
					// imgs.off('click.newtag');
					// enable nav area on img
					this.left.style.display = 'block';
					this.right.style.display = 'block';
				} else {
					this.tagMode = true;
					tagIcon.classList.replace('fa-toggle-off', 'fa-toggle-on')
					this.duringTagMode();
				}
			});
			tagButton.addEventListener('mouseenter', () => {
				if (this.tagMode) {
					tagIcon.classList.replace('fa-toggle-off', 'fa-toggle-on')
				} else {
					tagIcon.classList.replace('fa-toggle-on', 'fa-toggle-off')
				}
			});
			tagButton.addEventListener('mouseleave', () => {
				tagIcon.classList.remove('fa-toggle-on', 'fa-toggle-off');
				tagIcon.classList.add('fa-smile');
			});
			const screenIcon = select('.j-gallery-screen-icon', this.getElement())
			if (!screenIcon) {
				throw new Error("Geen screenIcon gevonden")
			}
			screenIcon.addEventListener('click', () => {
				if (this.preview.size !== Size.contain) {
					this.tagContainer.querySelectorAll('.fototag').forEach((tag) => tag.classList.add('verborgen'));
				} else {
					this.tagContainer.querySelectorAll('.fototag').forEach((tag) => tag.classList.remove('verborgen'));
				}
			});
		}

		protected goToItem(item: AlbumItem) {
			this.currentItem = item;
			return super.goToItem(item).then(() => {
				if (this.tagMode) {
					this.duringTagMode();
				}
				this.loadTags();
			});
		}

		private moveTagDivs() {
			this.tagContainer.querySelectorAll('.fototag')
				.forEach((t: HTMLElement) => this.moveTag(t, JSON.parse(t.dataset.tag ?? "{}")));
		}

		private moveTag(t: HTMLElement, tag: Tag) {
			const pos = this.getScreenPos(tag);
			t.style.height = pos.size + 'px';
			t.style.left = pos.x - pos.size / 2 + 'px';
			t.style.top = pos.y - pos.size / 2 + 'px';
			t.style.width = pos.size + 'px';
		}

		private moveTagForm() {
			if (!this.tagFormDiv) {
				return;
			}
			const pos = this.getScreenPos(JSON.parse(this.tagFormDiv.dataset.tagPosition ?? "{}") as Position);
			this.tagFormDiv.style.left = pos.x - (pos.size / 2) + 'px';
			this.tagFormDiv.style.top = pos.y + pos.size + 'px';
		}

		private getUrl() {
			return this.currentItem.fullUrl.replace(this.root, '');
		}

		private loadTags() {
			// remove old ones
			this.tagContainer.innerHTML = '';
			// get new ones
			const url = this.getUrl();
			const data = new FormData();
			data.append('foto', basename(decodeURI(url)));
			axios.post('/fotoalbum/gettags' + dirname(url), data)
				.then((response) => this.drawTags(response.data));
		}

		private drawTags(tags: Tag[]) {
			// remove old ones
			this.tagContainer.innerHTML = '';
			if (!Array.isArray(tags)) {
				return;
			}
			for (const tag of tags) {
				this.drawTag(tag);
			}
		}

		private getScreenPos(position: Position): Position {
			if (this.imageElement == null) {
				return {size: 0, x: 0, y: 0};
			}

			const parent = this.imageElement.parentElement;
			if (!parent) {
				throw new Error("ImageElement niet in DOM")
			}
			const w = this.imageElement.clientWidth;
			const h = this.imageElement.clientHeight;
			const fotoTopLeft = {
				x: (parent.clientWidth - w) / 2,
				y: (parent.clientHeight - h) / 2,
			};
			return {
				size: (w + h) / 200 * position.size,
				x: position.x * w / 100 + fotoTopLeft.x,
				y: position.y * h / 100 + fotoTopLeft.y,
			};
		}

		private removeTag(tagDiv: HTMLElement) {
			if (confirm('Etiket verwijderen?')) {
				const tag = JSON.parse(tagDiv.dataset.tag ?? "{}");
				const data = new FormData();
				data.append('refuuid', tag.refuuid);
				data.append('keyword', tag.keyword);
				axios
					.post('/fotoalbum/removetag', data)
					.then((response) => this.drawTags(response.data));
			}
		}

		private drawTag(tag: Tag) {
			const screenPosition = this.getScreenPos(tag);
			const tagDiv = createElement(`<div id="${tag.keyword}" class="fototag" title="${tag.name}"></div>`, {
				style: {
					height: screenPosition.size + 'px',
					left: screenPosition.x - screenPosition.size / 2 + 'px',
					top: screenPosition.y - screenPosition.size / 2 + 'px',
					width: screenPosition.size + 'px',
				},
			});
			this.tagContainer.appendChild(tagDiv);

			if (this.tagMode) {
				tagDiv.classList.add('showborder');
			}
			// set attr for move/resize
			tagDiv.dataset.tag = JSON.stringify(tag);
			// remove tag handler
			tagDiv.addEventListener('click', () => {
				if (this.activeTag) {
					this.activeTag.classList.remove('active');
				}
				this.activeTag = tagDiv;
				tagDiv.classList.add('active');
			});

			const tagMenu = createElement('<div id="tagMenu" class="dropdown-menu" role="menu"></div>', {
				style: {
					position: 'fixed',
					top: '0',
					left: '0',
				},
			});
			const btnDelTag = createElement(`<a id="btnDelTag" tabindex="-1" class="dropdown-item"><i class="fa fa-user-times"></i>&nbsp;Etiket verwijderen</a>`);
			tagMenu.appendChild(btnDelTag);
			tagDiv.appendChild(tagMenu);
			btnDelTag.addEventListener('click', () => this.removeTag(tagDiv));

			tagDiv.addEventListener('contextmenu', (e) => {
				e.preventDefault();
				tagMenu.style.display = 'block';
				tagMenu.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
			}, true);
			document.addEventListener('mouseup', () => {
				tagMenu.style.display = 'none';
			});
			// verberg tags tijdens zoomen
			if (this.preview.size !== Size.contain) {
				tagDiv.classList.add('verborgen');
			} else {
				tagDiv.classList.remove('verborgen');
			}
			return tagDiv;
		}

		private showTags() {
			this.tagContainer.querySelectorAll('.fototag').forEach((t) => t.classList.add('showborder'));
		}

		private hideTags() {
			this.tagContainer.querySelectorAll('.fototag').forEach((t) => t.classList.remove('showborder'));
		}

		private exitTagForm() {
			if (this.tagFormDiv) {
				this.tagFormDiv.remove();
			}
			this.tagFormDiv = null;
		}

		private duringTagMode() {
			if (this.preview.size !== Size.contain) { // if zoomed in
				alert('Je kunt niet inzoomen tijdens het etiketteren, dat werkt nog niet.');
			}
			if (this.tagFormDiv) {
				this.exitTagForm();
			}
			this.showTags();
			// disable nav area on img
			const left = this.previewElement.querySelector('.j-gallery-left') as HTMLElement;
			const right = this.previewElement.querySelector('.j-gallery-right') as HTMLElement;

			left.style.display = 'none';
			right.style.display = 'none';
			// click handler
			const img = this.imageElement;
			img.style.cursor = 'crosshair';
			// (re-)bind add new tag handler
			img.removeEventListener('click', this.newTagStart.bind(this));
			img.addEventListener('click', this.newTagStart.bind(this));
		}

		private addTag(position: Position) {
			const url = this.getUrl();
			const data = new FormData();
			data.append('foto', basename(decodeURI(url)));
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

		private drawTagForm(formHtml: string, position: Position) {
			const pos = this.getScreenPos(position);
			this.tagFormDiv = createElement(formHtml, {
				style: {
					left: pos.x - pos.size / 2 + 'px',
					position: 'absolute',
					top: pos.y + pos.size / 2 + 'px',
					zIndex: '10000',
				},
			});

			this.getElement().appendChild(this.tagFormDiv);

			const scripts = Array.from(this.tagFormDiv.querySelectorAll('script'));
			for (const script of scripts) {
				$.globalEval(script.innerText);
			}

			// set attr for move/resize
			this.tagFormDiv.dataset.tagPosition = JSON.stringify(position);
			// set submit handler
			const form = this.tagFormDiv.querySelector('form')
			if (!form) {
				throw new Error('tag div bevat geen form')
			}

			$(form).data('submitCallback', (response: Tag[] | string) => {
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
			setTimeout(() => {
				if (this.tagFormDiv) {
					const uidInput = this.tagFormDiv.querySelector('input[name="uid"]')

					if (!uidInput) {
						throw new Error("tagFormDiv bevat input met name uid")
					}

					uidInput.dispatchEvent(new Event('focus'))
				}

			});
		}

		private newTagStart(e: MouseEvent) {
			const img = this.imageElement;
			const target = e.target as HTMLElement;
			// calculate relative position to image top left
			const offset = $(target).offset();

			if (!offset) {
				throw new Error("Tag target heeft geen offset")
			}

			const width = img.clientWidth;
			const height = img.clientHeight;
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
			tagDiv.removeEventListener('click.tag', () => ({}));
			// resize-able
			tagDiv.style.cursor = 'nw-resize';

			const imgActive = this.imageElement;
			let prevX = 0;
			let prevY = 0;
			const windowMouseMoveListener = (e2: MouseEvent) => {
				newTag.size += (e2.pageX - prevX) * 100 / imgActive.clientWidth;
				newTag.size += (e2.pageY - prevY) * 100 / imgActive.clientHeight;
				prevX = e2.pageX;
				prevY = e2.pageY;
				if (newTag.size < 1) {
					newTag.size = 1;
				} else if (newTag.size > 99) {
					newTag.size = 99;
				}
				if (this.tagFormDiv) {
					const sizeInput = this.tagFormDiv.querySelector('input[name="size"]') as HTMLInputElement;
					sizeInput.value = String(Math.round(newTag.size));
				}
				this.moveTag(tagDiv, newTag);
				// update attr for move/resize
				tagDiv.dataset.size = String(newTag.size);
			};

			document.addEventListener('mouseup', () => document.removeEventListener('mousemove', windowMouseMoveListener));
			tagDiv.addEventListener('mousedown', (e1) => {
				prevX = e1.pageX;
				prevY = e1.pageY;
				document.addEventListener('mousemove', windowMouseMoveListener);
			});
		}
	};

export default withTags;
