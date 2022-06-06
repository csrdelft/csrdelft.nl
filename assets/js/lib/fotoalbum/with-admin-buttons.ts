import axios from 'axios';
import createElement from 'jgallery/src/utils/create-element';
import withTooltip from 'jgallery/src/utils/with-tooltip';
import AlbumItem from 'jgallery/types/album-item';
import { GalleryDecorator } from 'jgallery/types/gallery';
import Params from 'jgallery/types/gallery/parameters';
import { redirect, reload } from '../reload';
import { basename, dirname } from '../util';

const withAdminButtons: GalleryDecorator = (constructor) =>
	class extends constructor {
		private readonly root: string;
		private currentItem: AlbumItem;

		constructor(albums: AlbumItem[], params: Params) {
			super(albums, params);

			this.root = params.root || '';

			const container = withTooltip(
				createElement(
					'<span class="j-gallery-icon"><i class="fas fa-wrench" aria-hidden="true"></i></span>'
				),
				{
					style: {
						color: params.backgroundColor,
						background: params.textColor,
						transform: 'translateY(-8px)',
					},
					content: 'Beheer',
				}
			);

			container.addEventListener('click', () => {
				dropdown.style.display = 'flex';
			});
			container.addEventListener('mouseleave', () => {
				dropdown.style.display = 'none';
			});

			const rotateClockwiseButton = createElement(
				`<div><i class="fas fa-redo" aria-hidden="true"></i>&nbsp;Draai met de klok mee</div>`,
				{
					style: {
						padding: '0.2em',
					},
				}
			);
			rotateClockwiseButton.addEventListener('click', () => {
				const url = this.getUrl();
				axios
					.post('/fotoalbum/roteren' + dirname(url), {
						foto: basename(url),
						rotation: 90,
					})
					.then(reload);
			});

			const rotateCounterClockwiseButton = createElement(
				`<div><i class="fas fa-undo" aria-hidden="true"></i>&nbsp;Draai tegen de klok in</div>`,
				{
					style: {
						padding: '0.2em',
					},
				}
			);
			rotateCounterClockwiseButton.addEventListener('click', () => {
				const url = this.getUrl();
				axios
					.post('/fotoalbum/roteren' + dirname(url), {
						foto: basename(url),
						rotation: -90,
					})
					.then(reload);
			});

			const setCoverButton = createElement(
				`<div><i class="fas fa-folder" aria-hidden="true"></i>&nbsp;Instellen als albumcover</div>`,
				{
					style: {
						padding: '0.2em',
					},
				}
			);
			setCoverButton.addEventListener('click', () => {
				const url = this.getUrl();
				axios
					.post('/fotoalbum/albumcover' + dirname(url), {
						foto: basename(url),
					})
					.then((resp) => redirect(resp.data));
			});

			const deleteButton = createElement(
				`<div><i class="fas fa-xmark" aria-hidden="true"></i>&nbsp;Verwijderen</div>`,
				{
					style: {
						padding: '0.2em',
					},
				}
			);
			deleteButton.addEventListener('click', () => {
				if (!confirm('Foto definitief verwijderen. Weet u het zeker?')) {
					return false;
				}
				const url = this.getUrl();
				axios
					.post('/fotoalbum/verwijderen' + dirname(url), {
						foto: decodeURI(basename(url)),
					})
					.then(reload);
			});

			const dropdown = createElement('<div class="j-gallery-dropdown"></div>', {
				style: {
					background: params.backgroundColor,
					display: 'none',
					flexDirection: 'column',
					left: '0',
					position: 'absolute',
					top: '0',
					whiteSpace: 'nowrap',
				},
				children: [
					rotateClockwiseButton,
					rotateCounterClockwiseButton,
					setCoverButton,
					deleteButton,
				],
			});

			container.appendChild(dropdown);

			this.appendControlsElements([container]);
		}

		protected goToItem(item: AlbumItem) {
			this.currentItem = item;
			return super.goToItem(item);
		}

		private getUrl() {
			return this.currentItem.fullUrl.replace(this.root, '');
		}
	};

export default withAdminButtons;
