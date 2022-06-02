import { Size } from 'jgallery/src/preview';
import load from 'jgallery/src/utils/load';
import AlbumItem from 'jgallery/types/album-item';
import { GalleryDecorator } from 'jgallery/types/gallery';
import Params from 'jgallery/types/gallery/parameters';

const withFullSizePreview: GalleryDecorator = (constructor) =>
	class extends constructor {
		private currentItem: AlbumItem;

		constructor(albums: AlbumItem[], params: Params) {
			super(albums, params);

			const setSize = this.preview.setSize.bind(this.preview);

			this.preview.setSize = (size) => {
				this.updateImage(size);
				return setSize(size);
			};
		}

		protected async updateImage(size: Size) {
			const content = this.preview.getElement().querySelector<HTMLDivElement>('.j-gallery-preview-content');

			if (!content) {
				throw new Error('Geen content gevonden');
			}

			if (size === Size.auto) {
				await load(this.currentItem.fullUrl);

				const img = new Image();
				img.src = this.currentItem.fullUrl;
				content.style.backgroundImage = `url(${this.currentItem.fullUrl})`;
				content.style.backgroundSize = `${Math.min(3000, img.width)}px`;
			} else {
				content.style.backgroundImage = `url(${this.currentItem.url})`;
			}
		}
	};

export default withFullSizePreview;
